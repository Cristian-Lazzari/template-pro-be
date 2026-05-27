<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAutomationRequest;
use App\Http\Requests\Admin\UpdateAutomationRequest;
use App\Models\Automation;
use App\Models\Model as MailModel;
use App\Models\Promotion;
use App\Services\Marketing\Automation\AutomationTriggerEvaluator;
use App\Services\Marketing\AutomationAudienceBuilder;
use App\Services\Marketing\AutomationAssignmentService;
use App\Services\Marketing\AutomationRunMarkerService;
use App\Services\Marketing\MarketingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AutomationController extends Controller
{
    private const STATUS_TRANSLATION_KEYS = [
        'draft'    => 'status_draft',
        'active'   => 'status_active',
        'paused'   => 'status_paused',
        'archived' => 'status_archived',
    ];

    public function index()
    {
        $automations = Automation::query()
            ->with(['model', 'promotions'])
            ->orderBy('updated_at', 'desc')
            ->simplePaginate(40);

        $evaluator = app(AutomationTriggerEvaluator::class);

        return view('admin.Automations.index', [
            'automations'        => $automations,
            'statuses'           => $this->statusLabels(),
            'triggers'           => $this->triggerLabels($evaluator),
            'triggerDefinitions' => $evaluator->definitions(),
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

        $this->refreshAutomationRunMarker();

        return to_route('admin.automations.show', $automation)
            ->with('success', __('admin.marketing.automations.created_flash'));
    }

    public function show(Automation $automation, MarketingReportService $reportService)
    {
        $automation->load(['model', 'promotions']);
        $report = $reportService->forAutomation($automation);
        $customerPromotions = $automation->customerPromotions()
            ->with(['customer', 'promotion'])
            ->latest('created_at')
            ->paginate(50);

        $evaluator = app(AutomationTriggerEvaluator::class);

        return view('admin.Automations.show', [
            'automation'         => $automation,
            'report'             => $report,
            'customerPromotions' => $customerPromotions,
            'statuses'           => $this->statusLabels(),
            'triggers'           => $this->triggerLabels($evaluator),
            'triggerDefinitions' => $evaluator->definitions(),
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

        $this->refreshAutomationRunMarker();

        return to_route('admin.automations.show', $automation)
            ->with('success', __('admin.marketing.automations.updated_flash'));
    }

    public function activate(Automation $automation)
    {
        return $this->updateStatus($automation, 'active', __('admin.marketing.automations.activated_flash'));
    }

    public function pause(Automation $automation)
    {
        return $this->updateStatus($automation, 'paused', __('admin.marketing.automations.paused_flash'));
    }

    public function archive(Automation $automation)
    {
        return $this->updateStatus($automation, 'archived', __('admin.marketing.automations.archived_flash'));
    }

    public function draft(Automation $automation)
    {
        return $this->updateStatus($automation, 'draft', __('admin.marketing.automations.draft_flash'));
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
        $evaluator = app(AutomationTriggerEvaluator::class);

        return [
            'statuses'          => $this->statusLabels(),
            'triggers'          => $this->triggerLabels($evaluator),
            'triggerDefinitions' => $evaluator->definitions(),
            'mailModels'        => $this->mailModelOptions(),
            'promotions'        => Promotion::query()
                ->where('status', '!=', 'archived')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function statusLabels(): array
    {
        $labels = [];

        foreach (self::STATUS_TRANSLATION_KEYS as $status => $key) {
            $labels[$status] = __("admin.marketing.automations.{$key}");
        }

        return $labels;
    }

    /**
     * Build a flat key→label map from the evaluator for backward-compatible use in views.
     * Falls back to the trigger key itself if no lang entry exists.
     */
    private function triggerLabels(AutomationTriggerEvaluator $evaluator): array
    {
        $labels = [];

        foreach ($evaluator->definitions() as $key => $definition) {
            $langKey = "admin.marketing.automations.trigger_{$key}";
            $translated = __($langKey);
            $labels[$key] = $translated !== $langKey ? $translated : $definition['label'];
        }

        return $labels;
    }

    private function automationData(Request $request, ?Automation $automation = null): array
    {
        $data         = $request->validated();
        $formMetadata = $data['metadata'] ?? [];

        if (array_key_exists('cooldown_days', $formMetadata) && $formMetadata['cooldown_days'] !== null) {
            $formMetadata['cooldown_days'] = (int) $formMetadata['cooldown_days'];
        }

        unset($data['promotions'], $data['metadata']);
        $data['metadata'] = $formMetadata;

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

        $this->refreshAutomationRunMarker();

        return back()->with('success', $message);
    }

    private function refreshAutomationRunMarker(): void
    {
        try {
            app(AutomationRunMarkerService::class)->refresh();
        } catch (\Throwable) {
            // Marker refresh is best-effort; never block the HTTP response.
        }
    }
}
