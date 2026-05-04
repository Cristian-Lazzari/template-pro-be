<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAutomationRequest;
use App\Http\Requests\Admin\UpdateAutomationRequest;
use App\Models\Automation;
use App\Models\Model as MailModel;
use App\Models\Promotion;
use App\Services\Marketing\AutomationAudienceBuilder;
use App\Services\Marketing\AutomationAssignmentService;
use App\Services\Marketing\MarketingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AutomationController extends Controller
{
    private const STATUSES = [
        'draft' => 'Bozza',
        'active' => 'Attiva',
        'paused' => 'In pausa',
        'archived' => 'Archiviata',
    ];

    private const TRIGGERS = [
        'order_inactive_30_days' => '30 giorni inattivita ordine',
        'reservation_inactive_30_days' => '30 giorni inattivita prenotazione',
        'birthday' => 'Compleanno',
        'first_order_completed' => 'Primo ordine completato',
        'abandoned_profile' => 'Profilo abbandonato',
    ];

    public function index()
    {
        $automations = Automation::query()
            ->with(['model', 'promotions'])
            ->orderBy('updated_at', 'desc')
            ->simplePaginate(40);

        return view('admin.Automations.index', [
            'automations' => $automations,
            'statuses' => self::STATUSES,
            'triggers' => self::TRIGGERS,
        ]);
    }

    public function create()
    {
        $automation = new Automation([
            'status' => 'draft',
        ]);

        return view('admin.Automations.create', array_merge(
            $this->formOptions(),
            compact('automation')
        ));
    }

    public function store(StoreAutomationRequest $request)
    {
        $automation = Automation::query()->create(
            $this->automationData($request)
        );

        $automation->promotions()->sync($this->promotionIds($request));

        return to_route('admin.automations.show', $automation)
            ->with('success', 'Automazione creata correttamente.');
    }

    public function show(Automation $automation, MarketingReportService $reportService)
    {
        $automation->load(['model', 'promotions']);
        $report = $reportService->forAutomation($automation);
        $customerPromotions = $automation->customerPromotions()
            ->with(['customer', 'promotion'])
            ->latest('created_at')
            ->paginate(50);

        return view('admin.Automations.show', [
            'automation' => $automation,
            'report' => $report,
            'customerPromotions' => $customerPromotions,
            'statuses' => self::STATUSES,
            'triggers' => self::TRIGGERS,
        ]);
    }

    public function edit(Automation $automation)
    {
        $automation->load('promotions');

        return view('admin.Automations.edit', array_merge(
            $this->formOptions(),
            compact('automation')
        ));
    }

    public function update(UpdateAutomationRequest $request, Automation $automation)
    {
        $automation->update(
            $this->automationData($request, $automation)
        );

        $automation->promotions()->sync($this->promotionIds($request));

        return to_route('admin.automations.show', $automation)
            ->with('success', 'Automazione aggiornata correttamente.');
    }

    public function activate(Automation $automation)
    {
        return $this->updateStatus($automation, 'active', 'Automazione attivata correttamente.');
    }

    public function pause(Automation $automation)
    {
        return $this->updateStatus($automation, 'paused', 'Automazione messa in pausa correttamente.');
    }

    public function archive(Automation $automation)
    {
        return $this->updateStatus($automation, 'archived', 'Automazione archiviata correttamente.');
    }

    public function draft(Automation $automation)
    {
        return $this->updateStatus($automation, 'draft', 'Automazione salvata come bozza.');
    }

    public function previewAudience(Automation $automation, AutomationAudienceBuilder $audienceBuilder)
    {
        $promotionsCount = $automation->promotions()->count();
        $failureReason = $audienceBuilder->getFailureReason($automation);
        $canPreview = $failureReason === null;

        $result = [
            'automation_id' => $automation->id,
            'trigger' => $automation->trigger,
            'customers_checked' => $canPreview ? $audienceBuilder->countForAutomation($automation) : 0,
            'promotions_count' => $promotionsCount,
            'can_preview' => $canPreview,
            'failure_reason' => $failureReason,
        ];

        return back()->with('automation_audience_preview', $result);
    }

    public function prepareAssignments(Automation $automation, AutomationAssignmentService $assignmentService)
    {
        $result = $assignmentService->assign($automation, 500, false);

        return back()->with('automation_assignment_result', $result);
    }

    private function formOptions(): array
    {
        return [
            'statuses' => self::STATUSES,
            'triggers' => self::TRIGGERS,
            'mailModels' => $this->mailModelOptions(),
            'promotions' => Promotion::query()
                ->where('status', '!=', 'archived')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function automationData(Request $request, ?Automation $automation = null): array
    {
        $data = $request->validated();
        $metadata = is_array($automation?->metadata) ? $automation->metadata : [];
        $formMetadata = $data['metadata'] ?? [];

        foreach (['cooldown_days', 'enabled_from', 'enabled_until'] as $key) {
            if (array_key_exists($key, $formMetadata)) {
                $metadata[$key] = $key === 'cooldown_days' && $formMetadata[$key] !== null
                    ? (int) $formMetadata[$key]
                    : $formMetadata[$key];
            }
        }

        unset($data['promotions'], $data['metadata']);
        $data['metadata'] = $metadata;

        $data['status'] = $this->statusFromSubmitAction($request);
        unset($data['submit_action']);

        return $data;
    }

    private function statusFromSubmitAction(Request $request): string
    {
        return $request->input('submit_action') === 'activate' ? 'active' : 'draft';
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

    private function updateStatus(Automation $automation, string $status, string $message)
    {
        $automation->update(['status' => $status]);

        return back()->with('success', $message);
    }
}
