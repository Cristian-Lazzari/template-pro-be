<?php

namespace App\Services\Marketing;

use App\Mail\MarketingPromotionMail;
use App\Models\Automation;
use App\Models\Campaign;
use App\Models\CustomerPromotion;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MarketingEmailDispatchService
{
    private const MAX_ERRORS = 20;

    public function __construct(
        private MarketingTemplateRenderer $templateRenderer,
        private CustomerPromotionService $customerPromotionService
    ) {
    }

    public function previewCustomerPromotion(CustomerPromotion $customerPromotion): array
    {
        $readiness = $this->canSend($customerPromotion);
        $rendered = $readiness['rendered'] ?? $this->renderSafely($customerPromotion);

        return [
            'customer_promotion_id' => $customerPromotion->getKey(),
            'can_send' => $readiness['can_send'],
            'failure_reason' => $readiness['failure_reason'],
            'to' => $readiness['to'],
            'subject' => $rendered['subject'],
            'body_html' => $rendered['body_html'],
            'body_text' => $rendered['body_text'],
        ];
    }

    public function sendCustomerPromotion(CustomerPromotion $customerPromotion, bool $dryRun = true): array
    {
        $mode = $dryRun ? 'dry_run' : 'write';
        $result = $this->baseSendReport($customerPromotion, $mode);
        $readiness = $this->canSend($customerPromotion);

        $result['can_send'] = $readiness['can_send'];
        $result['failure_reason'] = $readiness['failure_reason'];
        $result['to'] = $readiness['to'];
        $result['already_sent'] = $customerPromotion->email_sent_at !== null;

        if (! $readiness['can_send']) {
            return $result;
        }

        if ($dryRun) {
            return $result;
        }

        try {
            Mail::to($readiness['to'])->send(
                new MarketingPromotionMail($readiness['rendered'])
            );

            $this->customerPromotionService->markSent($customerPromotion, false);
            $result['sent'] = true;

            return $result;
        } catch (Throwable $exception) {
            report($exception);

            $result['failure_reason'] = 'Email send failed.';

            return $result;
        }
    }

    public function sendForCampaign(Campaign $campaign, int $limit = 100, bool $dryRun = true): array
    {
        $result = $this->baseBatchReport($dryRun ? 'dry_run' : 'write', 'campaign_id', $campaign->getKey());
        $customerPromotions = $campaign->customerPromotions()
            ->with(['customer', 'promotion', 'campaign.model', 'automation.model'])
            ->whereNull('email_sent_at')
            ->whereNull('promo_used')
            ->whereHas('customer')
            ->whereHas('promotion')
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        return $this->sendBatch($customerPromotions, $result, $dryRun);
    }

    public function sendForAutomation(Automation $automation, int $limit = 100, bool $dryRun = true): array
    {
        $result = $this->baseBatchReport($dryRun ? 'dry_run' : 'write', 'automation_id', $automation->getKey());
        $customerPromotions = $automation->customerPromotions()
            ->with(['customer', 'promotion', 'campaign.model', 'automation.model'])
            ->whereNull('email_sent_at')
            ->whereNull('promo_used')
            ->whereHas('customer')
            ->whereHas('promotion')
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        return $this->sendBatch($customerPromotions, $result, $dryRun);
    }

    private function canSend(CustomerPromotion $customerPromotion): array
    {
        $customerPromotion->loadMissing([
            'automation.model',
            'campaign.model',
            'customer',
            'promotion',
        ]);

        if (! $customerPromotion->exists) {
            return $this->sendFailure('Customer promotion must be persisted before sending.');
        }

        if ($customerPromotion->email_sent_at !== null) {
            return $this->sendFailure('Email already sent.', null, alreadySent: true);
        }

        if (! $customerPromotion->customer) {
            return $this->sendFailure('Customer is missing.');
        }

        $to = trim((string) $customerPromotion->customer->email);

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $this->sendFailure('Customer email is missing or invalid.');
        }

        if ($customerPromotion->customer->marketing_consent_at === null) {
            return $this->sendFailure('Customer marketing consent is missing.', $to);
        }

        if (! $customerPromotion->promotion) {
            return $this->sendFailure('Promotion is missing.', $to);
        }

        if (! filled($customerPromotion->tracking_token)) {
            return $this->sendFailure('Tracking token is missing.', $to);
        }

        $rendered = $this->renderSafely($customerPromotion);

        if (! filled($rendered['subject']) || ! filled($rendered['body_html'])) {
            return $this->sendFailure('Template could not be rendered.', $to);
        }

        return [
            'can_send' => true,
            'failure_reason' => null,
            'to' => $to,
            'already_sent' => false,
            'rendered' => $rendered,
        ];
    }

    private function sendBatch($customerPromotions, array $result, bool $dryRun): array
    {
        $result['checked_count'] = $customerPromotions->count();

        foreach ($customerPromotions as $customerPromotion) {
            try {
                $sendResult = $this->sendCustomerPromotion($customerPromotion, $dryRun);

                if ($sendResult['already_sent']) {
                    $result['already_sent_count']++;

                    continue;
                }

                if ($dryRun && $sendResult['can_send']) {
                    $result['sent_count']++;

                    continue;
                }

                if ($sendResult['sent']) {
                    $result['sent_count']++;

                    continue;
                }

                $result['skipped_count']++;
            } catch (Throwable $exception) {
                $result['skipped_count']++;
                $this->addError($result, $customerPromotion->getKey(), $exception);
            }
        }

        return $result;
    }

    private function renderSafely(CustomerPromotion $customerPromotion): array
    {
        try {
            return $this->templateRenderer->render($customerPromotion);
        } catch (Throwable $exception) {
            report($exception);

            return $this->emptyRenderedTemplate();
        }
    }

    private function sendFailure(string $reason, ?string $to = null, bool $alreadySent = false): array
    {
        return [
            'can_send' => false,
            'failure_reason' => $reason,
            'to' => $to,
            'already_sent' => $alreadySent,
            'rendered' => null,
        ];
    }

    private function baseSendReport(CustomerPromotion $customerPromotion, string $mode): array
    {
        return [
            'mode' => $mode,
            'customer_promotion_id' => $customerPromotion->getKey(),
            'can_send' => false,
            'sent' => false,
            'already_sent' => false,
            'failure_reason' => null,
            'to' => null,
        ];
    }

    private function baseBatchReport(string $mode, string $contextKey, $contextId): array
    {
        return [
            'mode' => $mode,
            $contextKey => $contextId,
            'checked_count' => 0,
            'sent_count' => 0,
            'already_sent_count' => 0,
            'skipped_count' => 0,
            'errors_count' => 0,
            'errors' => [],
        ];
    }

    private function emptyRenderedTemplate(): array
    {
        return [
            'subject' => '',
            'body_html' => '',
            'body_text' => null,
            'tracking_open_url' => '',
            'tracking_click_url' => '',
        ];
    }

    private function addError(array &$result, $customerPromotionId, Throwable $exception): void
    {
        $result['errors_count']++;

        if (count($result['errors']) >= self::MAX_ERRORS) {
            return;
        }

        $result['errors'][] = [
            'customer_promotion_id' => $customerPromotionId,
            'message' => $exception->getMessage(),
        ];
    }
}
