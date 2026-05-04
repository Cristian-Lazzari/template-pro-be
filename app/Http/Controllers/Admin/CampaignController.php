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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CampaignController extends Controller
{
    private const STATUSES = [
        'draft' => 'Bozza',
        'active' => 'Attiva',
        'paused' => 'In pausa',
        'archived' => 'Archiviata',
        'sent' => 'Inviata',
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

    public function store(StoreCampaignRequest $request)
    {
        $campaign = Campaign::query()->create(
            $this->campaignData($request)
        );

        $campaign->promotions()->sync($this->promotionIds($request));

        return to_route('admin.campaigns.show', $campaign)
            ->with('success', 'Campagna creata correttamente.');
    }

    public function show(Campaign $campaign, MarketingReportService $reportService)
    {
        $campaign->load(['model', 'promotions']);
        $report = $reportService->forCampaign($campaign);
        $customerPromotions = $campaign->customerPromotions()
            ->with(['customer', 'promotion'])
            ->latest('created_at')
            ->paginate(50);

        return view('admin.Campaigns.show', [
            'campaign' => $campaign,
            'report' => $report,
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

    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return back()
                ->withErrors(['status' => 'Una campagna inviata puo essere solo consultata o archiviata.'])
                ->withInput();
        }

        $campaign->update(
            $this->campaignData($request, $campaign)
        );

        $campaign->promotions()->sync($this->promotionIds($request));

        return to_route('admin.campaigns.show', $campaign)
            ->with('success', 'Campagna aggiornata correttamente.');
    }

    public function activate(Campaign $campaign, CampaignAssignmentService $assignmentService)
    {
        if ($campaign->status === 'sent') {
            return back()->withErrors([
                'status' => 'Una campagna inviata puo essere solo archiviata.',
            ]);
        }

        $campaign->update(['status' => 'active']);
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
        unset($data['promotions']);

        if ($campaign?->exists) {
            unset($data['status']);
        } else {
            $data['status'] = 'draft';
        }

        return $data;
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
        if ($campaign->status === 'sent' && $status !== 'archived') {
            return back()->withErrors([
                'status' => 'Una campagna inviata puo essere solo archiviata.',
            ]);
        }

        $campaign->update(['status' => $status]);

        return back()->with('success', $message);
    }
}
