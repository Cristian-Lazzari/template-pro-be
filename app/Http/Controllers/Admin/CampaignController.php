<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Http\Requests\Admin\UpdateCampaignRequest;
use App\Models\Campaign;
use App\Models\Model as MailModel;
use App\Models\Promotion;
use App\Services\Marketing\CampaignAssignmentService;
use App\Services\Marketing\MarketingReportService;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

    private const SEGMENTS = [
        'all' => 'Tutti',
        'new_customers' => 'Nuovi clienti',
        'inactive_customers' => 'Clienti inattivi',
        'loyal_customers' => 'Clienti fedeli',
        'high_spending_customers' => 'Clienti alto valore',
    ];

    public function index()
    {
        $campaigns = Campaign::query()
            ->with(['model', 'promotions'])
            ->withCount([
                'customerPromotions',
                'customerPromotions as sent_customer_promotions_count' => function ($query) {
                    $query->whereNotNull('email_sent_at');
                },
            ])
            ->orderBy('updated_at', 'desc')
            ->simplePaginate(40);

        return view('admin.Campaigns.index', [
            'campaigns' => $campaigns,
            'statuses' => self::STATUSES,
            'segments' => self::SEGMENTS,
        ]);
    }

    public function create()
    {
        $campaign = new Campaign([
            'status' => 'draft',
            'segment' => 'all',
        ]);

        return view('admin.Campaigns.create', array_merge(
            $this->formOptions(),
            compact('campaign')
        ));
    }

    public function store(StoreCampaignRequest $request, CampaignAssignmentService $assignmentService)
    {
        $campaign = Campaign::query()->create(
            $this->campaignData($request)
        );

        $campaign->promotions()->sync($this->promotionIds($request));

        return $this->redirectAfterFormSave($campaign, $request, $assignmentService, 'Campagna creata correttamente.');
    }

    public function show(Campaign $campaign, MarketingReportService $reportService)
    {
        $campaign->load(['model', 'promotions']);
        $report = $reportService->forCampaign($campaign);
        $sendProgress = $this->sendProgress($campaign, $report);
        $customerPromotions = $campaign->customerPromotions()
            ->with(['customer', 'promotion'])
            ->latest('created_at')
            ->paginate(50);

        return view('admin.Campaigns.show', [
            'campaign' => $campaign,
            'report' => $report,
            'sendProgress' => $sendProgress,
            'customerPromotions' => $customerPromotions,
            'statuses' => self::STATUSES,
            'segments' => self::SEGMENTS,
        ]);
    }

    public function edit(Campaign $campaign)
    {
        $campaign->load('promotions');

        return view('admin.Campaigns.edit', array_merge(
            $this->formOptions(),
            compact('campaign')
        ));
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        if (in_array($campaign->status, ['completed', 'sent'], true)) {
            return back()
                ->withErrors(['status' => 'Una campagna completata puo essere solo consultata o archiviata.'])
                ->withInput();
        }

        $campaign->update(
            $this->campaignData($request, $campaign)
        );

        $campaign->promotions()->sync($this->promotionIds($request));

        return $this->redirectAfterFormSave($campaign, $request, $assignmentService, 'Campagna aggiornata correttamente.');
    }

    public function activate(Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        if (in_array($campaign->status, ['completed', 'sent'], true)) {
            return back()->withErrors([
                'status' => 'Una campagna completata puo essere solo archiviata.',
            ]);
        }

        $campaign->update(['status' => 'scheduled']);
        $campaign->refresh();

        $result = $assignmentService->assign($campaign, 500, false);
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

    public function draft(Campaign $campaign)
    {
        return $this->updateStatus($campaign, 'draft', 'Campagna salvata come bozza.');
    }

    public function previewAudience(Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        $result = $assignmentService->preview($campaign);

        return back()->with('audience_preview', $result);
    }

    public function prepareAssignments(Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        $result = $assignmentService->assign($campaign, 500, false);

        return back()->with('campaign_assignment_result', $result);
    }

    private function formOptions(): array
    {
        return [
            'statuses' => self::STATUSES,
            'segments' => self::SEGMENTS,
            'mailModels' => $this->mailModelOptions(),
            'promotions' => Promotion::query()
                ->where('status', '!=', 'archived')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function campaignData(Request $request, ?Campaign $campaign = null): array
    {
        $data = $request->validated();
        $data['status'] = $this->statusFromSubmitAction($request);
        unset($data['promotions'], $data['submit_action']);

        return $data;
    }

    private function statusFromSubmitAction(Request $request): string
    {
        return $request->input('submit_action') === 'activate' ? 'scheduled' : 'draft';
    }

    private function redirectAfterFormSave(
        Campaign $campaign,
        Request $request,
        CampaignAssignmentService $assignmentService,
        string $baseMessage
    ) {
        $redirect = to_route('admin.campaigns.show', $campaign);

        if ($request->input('submit_action') !== 'activate') {
            return $redirect->with('success', $baseMessage . ' Salvata come bozza.');
        }

        $campaign->refresh();
        $result = $assignmentService->assign($campaign, 500, false);
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

        return back()->with('success', $message);
    }

    private function sendProgress(Campaign $campaign, array $report): array
    {
        $status = $this->normalizedStatus($campaign->status);
        $total = (int) ($report['involved_count'] ?? 0);
        $sent = (int) ($report['sent_count'] ?? 0);
        $pending = max(0, $total - $sent);
        $percentage = $total > 0 ? round(($sent / $total) * 100, 2) : 0.0;

        return [
            'status' => $status,
            'label' => self::STATUSES[$status] ?? $status,
            'total' => $total,
            'sent' => $sent,
            'pending' => $pending,
            'percentage' => $percentage,
            'message' => $this->sendProgressMessage($campaign, $status, $sent, $total),
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

    private function sendProgressMessage(Campaign $campaign, string $status, int $sent, int $total): string
    {
        return match ($status) {
            'draft' => 'Bozza: completa la campagna e programmala per preparare le assegnazioni.',
            'scheduled' => $this->scheduledProgressMessage($campaign),
            'running' => "In esecuzione: {$sent} di {$total} email inviate.",
            'completed' => "Completata: {$sent} di {$total} email inviate.",
            'paused' => 'In pausa: la campagna e sospesa e non inviera nuove email.',
            'archived' => 'Archiviata.',
            default => self::STATUSES[$status] ?? $status,
        };
    }

    private function scheduledProgressMessage(Campaign $campaign): string
    {
        if (! $campaign->scheduled_at) {
            return 'Programmazione mancante: imposta data e ora per consentire allo scheduler di partire.';
        }

        if ($campaign->scheduled_at->isFuture()) {
            return 'Programmata: invio tra circa ' . $campaign->scheduled_at->diffForHumans(
                now(),
                [
                    'parts' => 2,
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                ]
            ) . '.';
        }

        return 'Programmata: pronta per l’invio al prossimo scheduler.';
    }
}
