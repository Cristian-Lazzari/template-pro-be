<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use App\Models\Customer;
use App\Services\Marketing\Automation\AutomationTriggerEvaluator;
use App\Services\Marketing\Automation\ProfilingConsentTriggerContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AutomationAudienceBuilder
{
    private const MAX_CUSTOMERS_LIMIT = 500;

    private ?array $customerColumns = null;

    public function __construct(
        private MarketingConsentService $marketingConsentService,
        private AutomationTriggerEvaluator $triggerEvaluator
    ) {
    }

    public function queryForAutomation(Automation $automation): Builder
    {
        $query = Customer::query();

        // NON applichiamo qui il filtro email_marketing_consent_at.
        // Questo builder costruisce l'audience per l'ASSEGNAZIONE della promozione,
        // non per l'invio email. I due livelli sono separati:
        //   • Promozione in account   → nessun filtro consenso email richiesto
        //   • Invio email promozionale → consenso controllato in MarketingEmailDispatchService::canSend()
        //
        // Motivazione: un cliente senza consenso email può comunque ricevere la promozione
        // nel proprio account (dashboard cliente), ma non riceverà l'email finché non
        // fornisce il consenso.

        $trigger = $automation->trigger;
        $params  = is_array($automation->metadata) ? $automation->metadata : [];

        if ($trigger === null || ! $this->triggerEvaluator->supports($trigger)) {
            $this->applyEmptyResult($query);

            return $query;
        }

        $triggerObj = $this->triggerEvaluator->get($trigger);
        $failure    = $triggerObj->getFailureReason($params, $this->customerColumns());

        if ($failure !== null) {
            $this->applyEmptyResult($query);

            return $query;
        }

        // Filtro profilazione: applicato solo per trigger che richiedono tracking_consent_at.
        // Es. birthday_before usa la data di nascita a fini promozionali → richiede consenso.
        // I trigger su dati transazionali (ordini, prenotazioni) non richiedono questo filtro
        // perché la base giuridica è l'esecuzione del contratto.
        if ($triggerObj instanceof ProfilingConsentTriggerContract && $triggerObj->requiresProfilingConsent()) {
            $this->marketingConsentService->applyTrackingConsent($query);
        }

        $triggerObj->applyToQuery($query, $params);

        return $this->applyDefaultOrder($query);
    }

    public function countForAutomation(Automation $automation): int
    {
        return $this->queryForAutomation($automation)->count();
    }

    public function getCustomersForAutomation(Automation $automation, int $limit = self::MAX_CUSTOMERS_LIMIT): Collection
    {
        $limit = max(1, min($limit, self::MAX_CUSTOMERS_LIMIT));

        return $this->queryForAutomation($automation)
            ->limit($limit)
            ->get();
    }

    /**
     * Return null if the automation can be queried, or a human-readable reason why it cannot.
     * Called by AutomationAssignmentService and AutomationController::previewAudience.
     */
    public function getFailureReason(Automation $automation): ?string
    {
        if ($automation->status === 'archived') {
            return 'Automazione archiviata.';
        }

        if (! $automation->trigger) {
            return 'Trigger mancante.';
        }

        if (! $this->triggerEvaluator->supports($automation->trigger)) {
            return "Trigger '{$automation->trigger}' non supportato.";
        }

        $params  = is_array($automation->metadata) ? $automation->metadata : [];
        $failure = $this->triggerEvaluator->getFailureReason($automation->trigger, $params);

        if ($failure !== null) {
            return $failure;
        }

        $windowFailure = $this->getActiveWindowFailureReason($params);

        if ($windowFailure !== null) {
            return $windowFailure;
        }

        if ($automation->promotions()->count() === 0) {
            return 'Nessuna promozione collegata.';
        }

        return null;
    }

    private function getActiveWindowFailureReason(array $metadata): ?string
    {
        $now = now()->startOfDay();

        $from = data_get($metadata, 'enabled_from');

        if ($from) {
            try {
                if ($now->lt(Carbon::parse($from)->startOfDay())) {
                    return 'Automazione non ancora attiva (enabled_from nel futuro).';
                }
            } catch (\Throwable) {
            }
        }

        $until = data_get($metadata, 'enabled_until');

        if ($until) {
            try {
                if ($now->gt(Carbon::parse($until)->startOfDay())) {
                    return 'Automazione scaduta (enabled_until superato).';
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function applyDefaultOrder(Builder $query): Builder
    {
        if ($this->hasCustomerColumn('last_activity_at')) {
            return $query
                ->orderByDesc('last_activity_at')
                ->orderByDesc('created_at');
        }

        return $query->orderByDesc('created_at');
    }

    private function applyEmptyResult(Builder $query): void
    {
        $query->whereNull('id')->whereNotNull('id');
    }

    private function hasCustomerColumn(string $column): bool
    {
        return array_key_exists($column, $this->customerColumns());
    }

    private function customerColumns(): array
    {
        if ($this->customerColumns !== null) {
            return $this->customerColumns;
        }

        if (! Schema::hasTable('customers')) {
            return $this->customerColumns = [];
        }

        return $this->customerColumns = array_flip(Schema::getColumnListing('customers'));
    }
}
