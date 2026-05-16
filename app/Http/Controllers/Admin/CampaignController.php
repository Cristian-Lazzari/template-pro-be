<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Http\Requests\Admin\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Models\Model as MailModel;
use App\Models\Promotion;
use App\Services\Marketing\CampaignAssignmentService;
use App\Services\Marketing\CampaignScheduleService;
use App\Services\Marketing\MarketingCustomerSegmentService;
use App\Services\Marketing\MarketingRunMarkerService;
use App\Services\Marketing\MarketingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignController extends Controller
{
    private const STATUSES = [
        'draft' => 'Bozza',
        'scheduled' => 'Programmata',
        'running' => 'In esecuzione',
        'completed' => 'Completata',
        'paused' => 'In pausa',
        'archived' => 'Archiviata',
        'active' => 'Programmata',
        'sent' => 'Completata',
    ];

    public function index(MarketingCustomerSegmentService $segmentService)
    {
        $campaigns = $this->campaignListQuery()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'archived');
            })
            ->orderBy('updated_at', 'desc')
            ->simplePaginate(40);

        return view('admin.Campaigns.index', [
            'campaigns' => $campaigns,
            'statuses' => self::STATUSES,
            'segments' => $segmentService->getSegmentOptions(),
            'scheduleWindows' => app(CampaignScheduleService::class)->getWindowOptions(),
            'isArchivedView' => false,
        ]);
    }

    public function archived(MarketingCustomerSegmentService $segmentService)
    {
        $campaigns = $this->campaignListQuery()
            ->where('status', 'archived')
            ->orderBy('updated_at', 'desc')
            ->simplePaginate(40);

        return view('admin.Campaigns.index', [
            'campaigns' => $campaigns,
            'statuses' => self::STATUSES,
            'segments' => $segmentService->getSegmentOptions(),
            'scheduleWindows' => app(CampaignScheduleService::class)->getWindowOptions(),
            'isArchivedView' => true,
        ]);
    }

    public function create()
    {
        $campaign = new Campaign([
            'status' => 'draft',
            'campaign_type' => Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING,
            'channel' => Campaign::CHANNEL_EMAIL,
            'consent_basis' => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
            'segment' => 'all',
        ]);

        return view('admin.Campaigns.create', array_merge(
            $this->formOptions($campaign),
            compact('campaign')
        ));
    }

    public function store(
        StoreCampaignRequest $request,
        CampaignAssignmentService $assignmentService,
        CampaignScheduleService $scheduleService,
        MarketingCustomerSegmentService $segmentService
    )
    {
        $campaign = Campaign::query()->create(
            $this->campaignData($request, $scheduleService, null, $segmentService)
        );

        $campaign->promotions()->sync($this->promotionIds($request));

        return $this->redirectAfterFormSave($campaign, $request, $assignmentService, $scheduleService, 'Campagna creata correttamente.');
    }

    public function show(
        Campaign $campaign,
        MarketingReportService $reportService,
        MarketingCustomerSegmentService $segmentService
    )
    {
        $campaign->load(['model', 'promotions.targets']);
        $report = $reportService->forCampaign($campaign);
        $sendProgress = $this->sendProgress($campaign);
        $normalizedStatus = $this->normalizedStatus($campaign->status);
        $campaignPromotionCaseUses = $campaign->promotions
            ->pluck('case_use')
            ->filter()
            ->unique()
            ->values();
        $hasLinkedPromotions = $campaign->promotions->isNotEmpty();
        $hasAssignments = (int) $sendProgress['involved_count'] > 0;
        $canPreviewAudience = $normalizedStatus === 'draft'
            && ! $hasAssignments
            && $hasLinkedPromotions;
        $canPrepareAssignments = $canPreviewAudience;
        $showOrderMetrics = $hasLinkedPromotions
            && $campaignPromotionCaseUses->intersect(['take_away', 'delivery', 'generic'])->isNotEmpty();
        $showReservationMetrics = $hasLinkedPromotions
            && $campaignPromotionCaseUses->intersect(['table', 'generic'])->isNotEmpty();
        $customerPromotions = $campaign->customerPromotions()
            ->with(['customer', 'promotion.targets'])
            ->latest('created_at')
            ->paginate(50);

        return view('admin.Campaigns.show', [
            'campaign' => $campaign,
            'report' => $report,
            'sendProgress' => $sendProgress,
            'hasAssignments' => $hasAssignments,
            'canPreviewAudience' => $canPreviewAudience,
            'canPrepareAssignments' => $canPrepareAssignments,
            'canActivateCampaign' => in_array($normalizedStatus, ['draft', 'paused'], true),
            'canPauseCampaign' => in_array($normalizedStatus, ['scheduled', 'running'], true),
            'canDraftCampaign' => in_array($normalizedStatus, ['scheduled', 'running', 'paused'], true),
            'canArchiveCampaign' => in_array($normalizedStatus, ['draft', 'scheduled', 'running', 'paused', 'completed'], true),
            'canRestoreCampaign' => $normalizedStatus === 'archived',
            'canDestroyCampaign' => $normalizedStatus === 'archived' && ! $hasAssignments,
            'involvedCount' => $sendProgress['involved_count'],
            'sentCount' => $sendProgress['sent_count'],
            'pendingCount' => $sendProgress['pending_count'],
            'progressPercentage' => $sendProgress['progress_percentage'],
            'nextBatchDueAt' => $sendProgress['next_batch_due_at'],
            'estimatedDurationMinutes' => $sendProgress['estimated_duration_minutes'],
            'completedAt' => $sendProgress['completed_at'],
            'campaignPromotionCaseUses' => $campaignPromotionCaseUses,
            'hasLinkedPromotions' => $hasLinkedPromotions,
            'showOrderMetrics' => $showOrderMetrics,
            'showReservationMetrics' => $showReservationMetrics,
            'customerPromotions' => $customerPromotions,
            'statuses' => self::STATUSES,
            'segments' => $segmentService->getSegmentOptions(),
            'scheduleWindows' => app(CampaignScheduleService::class)->getWindowOptions(),
            'consentBasisOptions' => Campaign::consentBasisOptions(),
        ]);
    }

    public function edit(Campaign $campaign)
    {
        $campaign->load(['model', 'promotions']);

        return view('admin.Campaigns.edit', array_merge(
            $this->formOptions($campaign),
            compact('campaign')
        ));
    }

    public function update(
        UpdateCampaignRequest $request,
        Campaign $campaign,
        CampaignAssignmentService $assignmentService,
        CampaignScheduleService $scheduleService,
        MarketingCustomerSegmentService $segmentService
    )
    {
        if (in_array($campaign->status, ['completed', 'sent'], true)) {
            return back()
                ->withErrors(['status' => 'Una campagna completata puo essere solo consultata o archiviata.'])
                ->withInput();
        }

        $campaign->update(
            $this->campaignData($request, $scheduleService, $campaign, $segmentService)
        );

        $campaign->promotions()->sync($this->promotionIds($request));

        return $this->redirectAfterFormSave($campaign, $request, $assignmentService, $scheduleService, 'Campagna aggiornata correttamente.');
    }

    public function activate(
        Campaign $campaign,
        CampaignAssignmentService $assignmentService,
        CampaignScheduleService $scheduleService
    )
    {
        if (in_array($campaign->status, ['completed', 'sent'], true)) {
            return back()->withErrors([
                'status' => 'Una campagna completata puo essere solo archiviata.',
            ]);
        }

        $campaign->update($this->scheduleExistingCampaignData($campaign, $scheduleService));
        $campaign->refresh();

        $result = $assignmentService->assign($campaign, 500, false);
        $this->updateEstimatedDuration($campaign, $scheduleService);
        $this->refreshMarketingRunMarker();

        $message = $campaign->scheduled_at
            ? 'Campagna confermata. Le assegnazioni sono state preparate; l’invio partira dalla programmazione.'
            : 'Campagna confermata. Le assegnazioni sono state preparate; imposta una programmazione per avviare l’invio automatico.';

        return back()
            ->with('success', $message)
            ->with('campaign_assignment_result', $result);
    }

    public function pause(Campaign $campaign)
    {
        return $this->updateStatus($campaign, 'paused', 'Campagna messa in pausa correttamente.');
    }

    public function archive(Campaign $campaign)
    {
        return $this->updateStatus($campaign, 'archived', 'Campagna archiviata correttamente.');
    }

    public function restore(Campaign $campaign)
    {
        if ($this->normalizedStatus($campaign->status) !== 'archived') {
            return back()->withErrors([
                'status' => 'Solo una campagna archiviata puo essere ripristinata.',
            ]);
        }

        $campaign->update(['status' => 'draft']);
        $this->refreshMarketingRunMarker();

        return to_route('admin.campaigns.show', $campaign)
            ->with('success', 'Campagna ripristinata come bozza.');
    }

    public function destroy(Campaign $campaign)
    {
        if ($this->normalizedStatus($campaign->status) === 'draft') {
            $campaignName = $campaign->name;

            DB::transaction(function () use ($campaign) {
                $campaign->promotions()->detach();
                $campaign->customerPromotions()->delete();
                $campaign->delete();
            });

            $this->refreshMarketingRunMarker();

            return to_route('admin.campaigns.index')
                ->with('success', 'Bozza "' . $campaignName . '" eliminata insieme ai suoi collegamenti.');
        }

        if ($this->normalizedStatus($campaign->status) !== 'archived') {
            return back()->withErrors([
                'status' => 'Puoi eliminare direttamente solo le bozze. Per le altre campagne usa Archivia.',
            ]);
        }

        if ($campaign->customerPromotions()->exists()) {
            return back()->withErrors([
                'status' => 'Impossibile eliminare definitivamente: la campagna ha assegnazioni/storico collegato. Puoi archiviarla.',
            ]);
        }

        $campaignName = $campaign->name;

        DB::transaction(function () use ($campaign) {
            $campaign->promotions()->detach();
            $campaign->delete();
        });

        $this->refreshMarketingRunMarker();

        return to_route('admin.campaigns.archived')
            ->with('success', 'Campagna "' . $campaignName . '" eliminata definitivamente.');
    }

    public function draft(Campaign $campaign)
    {
        return $this->updateStatus($campaign, 'draft', 'Campagna salvata come bozza.');
    }

    public function previewAudience(Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        $result = $assignmentService->preview($campaign);

        return back()->with('audience_preview', $result);
    }

    public function audiencePreview(
        Request $request,
        CampaignAssignmentService $assignmentService,
        MarketingCustomerSegmentService $segmentService
    ) {
        $data = $request->validate([
            'campaign_id' => ['nullable', 'integer', 'exists:campaigns,id'],
            'campaign_type' => ['nullable', Rule::in(Campaign::campaignTypeValues())],
            'consent_basis' => ['nullable', Rule::in(Campaign::consentBasisValues())],
            'segment' => ['nullable', 'string'],
            'promotions' => ['nullable', 'array'],
            'promotions.*' => ['integer', 'exists:promotions,id'],
        ]);
        $campaignType = Campaign::normalizeCampaignType(
            ($data['campaign_type'] ?? null) ?: Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING
        );
        $requestedSegment = trim((string) ($data['segment'] ?? ''));

        if ($requestedSegment === '') {
            return response()->json([
                'matched' => 0,
                'available' => 0,
                'message' => 'Seleziona un segmento per vedere la stima.',
            ]);
        }

        if (! $segmentService->isValidSegmentForCampaignType($requestedSegment, $campaignType)) {
            throw ValidationException::withMessages([
                'segment' => 'Il segmento selezionato non e valido per il tipo di campagna.',
            ]);
        }

        $consentBasis = Campaign::consentBasisForCampaignType($campaignType);
        $campaign = isset($data['campaign_id'])
            ? Campaign::query()->findOrFail($data['campaign_id'])
            : new Campaign(['status' => 'draft']);

        $campaign->forceFill([
            'campaign_type' => $campaignType,
            'channel' => Campaign::channelForConsentBasis($consentBasis),
            'consent_basis' => $consentBasis,
            'segment' => $segmentService->normalizeSegmentForCampaignType($requestedSegment, $campaignType),
        ]);

        $result = $assignmentService->previewSelection(
            $campaign,
            array_values((array) ($data['promotions'] ?? []))
        );

        return response()->json([
            'matched' => (int) ($result['matched_count'] ?? 0),
            'available' => (int) ($result['available_count'] ?? 0),
            'assignable' => (int) ($result['assignable_count'] ?? 0),
            'customers_checked' => (int) ($result['customers_checked'] ?? 0),
            'promotions_count' => (int) ($result['promotions_count'] ?? 0),
            'can_assign' => (bool) ($result['can_assign'] ?? false),
            'failure_reason' => $result['failure_reason'] ?? null,
            'message' => $this->audiencePreviewMessage($result),
            'campaign_type' => $campaignType,
            'consent_basis' => $consentBasis,
            'segment' => $campaign->segment,
            'available_label' => $this->audiencePreviewAvailableLabel($campaignType),
        ]);
    }

    public function prepareAssignments(Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        $result = $assignmentService->assign($campaign, 500, false);
        $this->refreshMarketingRunMarker();

        return back()->with('campaign_assignment_result', $result);
    }

    private function formOptions(?Campaign $campaign = null): array
    {
        $segmentService = app(MarketingCustomerSegmentService::class);
        $segments = $segmentService->getSegmentOptions();

        return [
            'statuses' => self::STATUSES,
            'segments' => $segments,
            'campaignTypeOptions' => Campaign::campaignTypeOptions(),
            'consentBasisOptions' => Campaign::consentBasisOptions(),
            'audiencePreviewUrl' => route('admin.campaigns.audience-preview'),
            'scheduleWindows' => $this->campaignFormScheduleWindows(),
            'mailModels' => $this->mailModelOptions(),
            'promotions' => Promotion::query()
                ->where('status', '!=', 'archived')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function campaignListQuery()
    {
        return Campaign::query()
            ->with(['model', 'promotions'])
            ->withCount([
                'customerPromotions',
                'customerPromotions as sent_customer_promotions_count' => function ($query) {
                    $query->whereNotNull('email_sent_at');
                },
            ]);
    }

    private function campaignData(
        Request $request,
        CampaignScheduleService $scheduleService,
        ?Campaign $campaign = null,
        ?MarketingCustomerSegmentService $segmentService = null
    ): array
    {
        $data = $request->validated();
        $segmentService ??= app(MarketingCustomerSegmentService::class);
        $data['campaign_type'] = Campaign::normalizeCampaignType(
            ($data['campaign_type'] ?? null) ?: ($campaign?->campaign_type ?? Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING)
        );
        $data['segment'] = $segmentService->normalizeSegmentForCampaignType($data['segment'] ?? null, $data['campaign_type']);
        $data['consent_basis'] = Campaign::consentBasisForCampaignType($data['campaign_type']);
        $data['channel'] = Campaign::channelForConsentBasis($data['consent_basis']);
        $metadata = is_array($campaign?->metadata) ? $campaign->metadata : [];
        $requestedAt = $request->input('scheduled_at');
        $scheduleWindow = $request->input('schedule_window') ?: ($requestedAt ? 'custom' : 'next_available');

        if ($request->input('submit_action') === 'activate') {
            $scheduledAt = $scheduleService->normalizeScheduledAt($requestedAt, $scheduleWindow, $campaign);
            $data['scheduled_at'] = $scheduledAt;
            $metadata['schedule_window'] = $scheduleWindow;
            $metadata['estimated_duration_minutes'] = $scheduleService->estimateCampaignDurationMinutes($campaign);

            if ($requestedAt) {
                $metadata['requested_scheduled_at'] = $requestedAt;
            } else {
                unset($metadata['requested_scheduled_at']);
            }
        } else {
            if ($requestedAt === null || $requestedAt === '') {
                $data['scheduled_at'] = null;
            }

            if ($request->filled('schedule_window')) {
                $metadata['schedule_window'] = $scheduleWindow;
            }
        }

        $data['status'] = $this->statusFromSubmitAction($request);
        $data['metadata'] = $metadata;
        unset($data['promotions'], $data['submit_action'], $data['schedule_window']);

        return $data;
    }

    private function campaignFormScheduleWindows(): array
    {
        $scheduleWindows = app(CampaignScheduleService::class)->getWindowOptions();
        unset($scheduleWindows['custom']);

        return $scheduleWindows;
    }

    private function audiencePreviewMessage(array $result): ?string
    {
        if (($result['failure_reason'] ?? null) === 'Campaign must have at least one promotion.') {
            return 'Seleziona almeno una promozione per stimare i destinatari effettivi.';
        }

        if (($result['failure_reason'] ?? null) !== null) {
            return 'Non e possibile calcolare la preview audience con la selezione corrente.';
        }

        if ((int) ($result['matched_count'] ?? 0) === 0) {
            return 'Nessun cliente raggiungibile con segmento, consenso e promozioni selezionati.';
        }

        return null;
    }

    private function audiencePreviewAvailableLabel(string $campaignType): string
    {
        return match ($campaignType) {
            Campaign::CAMPAIGN_TYPE_SOFT_MARKETING => 'Clienti con email valida senza opt-out soft',
            Campaign::CAMPAIGN_TYPE_PROFILING => 'Clienti con email, consenso marketing e profilazione',
            default => 'Clienti con email e consenso esplicito',
        };
    }

    private function statusFromSubmitAction(Request $request): string
    {
        return $request->input('submit_action') === 'activate' ? 'scheduled' : 'draft';
    }

    private function redirectAfterFormSave(
        Campaign $campaign,
        Request $request,
        CampaignAssignmentService $assignmentService,
        CampaignScheduleService $scheduleService,
        string $baseMessage
    ) {
        if ($request->input('submit_action') !== 'activate') {
            $this->refreshMarketingRunMarker();

            return to_route('admin.campaigns.index')
                ->with('success', 'Campagna salvata come bozza. Puoi completarla dalla lista campagne.');
        }

        $redirect = to_route('admin.campaigns.show', $campaign);
        $campaign->refresh();
        $result = $assignmentService->assign($campaign, 500, false);
        $this->updateEstimatedDuration($campaign, $scheduleService);
        $this->refreshMarketingRunMarker();

        $message = $campaign->scheduled_at
            ? $baseMessage . ' Assegnazioni preparate; le email partiranno all’orario programmato tramite scheduler.'
            : $baseMessage . ' Assegnazioni preparate; imposta una programmazione per avviare l’invio automatico.';

        return $redirect
            ->with('success', $message)
            ->with('campaign_assignment_result', $result);
    }

    private function promotionIds(Request $request): array
    {
        return array_values(array_filter((array) $request->input('promotions', [])));
    }

    private function scheduleExistingCampaignData(Campaign $campaign, CampaignScheduleService $scheduleService): array
    {
        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $requestedAt = $campaign->scheduled_at?->toDateTimeString();
        $scheduleWindow = data_get($metadata, 'schedule_window') ?: ($requestedAt ? 'custom' : 'next_available');
        $scheduledAt = $scheduleService->normalizeScheduledAt($requestedAt, $scheduleWindow, $campaign);

        $metadata['schedule_window'] = $scheduleWindow;
        $metadata['estimated_duration_minutes'] = $scheduleService->estimateCampaignDurationMinutes($campaign);

        if ($requestedAt) {
            $metadata['requested_scheduled_at'] = $requestedAt;
        } else {
            unset($metadata['requested_scheduled_at']);
        }

        return [
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'metadata' => $metadata,
        ];
    }

    private function updateEstimatedDuration(Campaign $campaign, CampaignScheduleService $scheduleService): void
    {
        $campaign->refresh();
        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $metadata['estimated_duration_minutes'] = $scheduleService->estimateCampaignDurationMinutes($campaign);

        $campaign->forceFill(['metadata' => $metadata])->save();
    }

    private function mailModelOptions()
    {
        if (! Schema::hasTable('models')) {
            return collect();
        }

        $hasType = Schema::hasColumn('models', 'type');
        $hasChannel = Schema::hasColumn('models', 'channel');

        return MailModel::query()
            ->when($hasType || $hasChannel, function ($query) use ($hasType, $hasChannel) {
                $query->where(function ($nested) use ($hasType, $hasChannel) {
                    if ($hasType) {
                        $nested->orWhere('type', 'marketing');
                    }

                    if ($hasChannel) {
                        $nested->orWhere('channel', 'email');
                    }
                });
            })
            ->orderBy('name')
            ->get();
    }

    private function updateStatus(Campaign $campaign, string $status, string $message)
    {
        if (in_array($campaign->status, ['completed', 'sent'], true) && $status !== 'archived') {
            return back()->withErrors([
                'status' => 'Una campagna completata puo essere solo archiviata.',
            ]);
        }

        $campaign->update(['status' => $status]);
        $this->refreshMarketingRunMarker();

        return back()->with('success', $message);
    }

    private function refreshMarketingRunMarker(): void
    {
        try {
            app(MarketingRunMarkerService::class)->refresh();
        } catch (\Throwable $exception) {
            Log::warning('Unable to refresh marketing run marker.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendProgress(Campaign $campaign): array
    {
        $status = $this->normalizedStatus($campaign->status);
        $total = $campaign->customerPromotions()->count();
        $sent = $campaign->customerPromotions()->whereNotNull('email_sent_at')->count();
        $pending = max(0, $total - $sent);
        $percentage = match (true) {
            $total === 0 => 0.0,
            $status === 'completed' => 100.0,
            default => round(($sent / $total) * 100, 2),
        };
        $nextBatchDueAt = $this->metadataDate($campaign, 'next_batch_due_at');
        $completedAt = $campaign->sent_at ?: $this->metadataDate($campaign, 'completed_at');
        $estimatedDurationMinutes = data_get($campaign->metadata, 'estimated_duration_minutes');

        return [
            'status' => $status,
            'label' => self::STATUSES[$status] ?? $status,
            'involved_count' => $total,
            'sent_count' => $sent,
            'pending_count' => $pending,
            'progress_percentage' => $percentage,
            'total' => $total,
            'sent' => $sent,
            'pending' => $pending,
            'percentage' => $percentage,
            'next_batch_due_at' => $nextBatchDueAt,
            'estimated_duration_minutes' => $estimatedDurationMinutes,
            'completed_at' => $completedAt,
            'message' => $this->sendProgressMessage($campaign, $status, $sent, $total, $completedAt),
        ];
    }

    private function normalizedStatus(?string $status): string
    {
        return match ($status) {
            'active' => 'scheduled',
            'sent' => 'completed',
            default => $status ?: 'draft',
        };
    }

    private function sendProgressMessage(
        Campaign $campaign,
        string $status,
        int $sent,
        int $total,
        ?Carbon $completedAt = null
    ): string
    {
        return match ($status) {
            'draft' => 'Bozza: programma la campagna per creare i destinatari.',
            'scheduled' => $this->scheduledProgressMessage($campaign),
            'running' => "Invio in corso: {$sent} di {$total} email inviate.",
            'completed' => $completedAt
                ? 'Campagna completata il ' . $completedAt->format('d/m/Y H:i')
                : 'Campagna completata.',
            'paused' => 'Campagna in pausa: non verranno inviati nuovi batch.',
            'archived' => 'Campagna archiviata.',
            default => self::STATUSES[$status] ?? $status,
        };
    }

    private function scheduledProgressMessage(Campaign $campaign): string
    {
        if (! $campaign->scheduled_at) {
            return 'Programmazione non impostata.';
        }

        if ($campaign->scheduled_at->isFuture()) {
            return 'Invio programmato per: ' . $campaign->scheduled_at->format('d/m/Y H:i');
        }

        return 'Pronta per il prossimo ciclo del runner marketing.';
    }

    private function metadataDate(Campaign $campaign, string $key): ?Carbon
    {
        $value = data_get($campaign->metadata, $key);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
