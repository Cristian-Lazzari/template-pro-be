<?php

namespace App\Console\Commands\Marketing;

use App\Models\Automation;
use App\Services\Marketing\AutomationAssignmentService;
use App\Services\Marketing\AutomationRunMarkerService;
use App\Services\Marketing\MarketingEmailDispatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessAutomationEmailsCommand extends Command
{
    protected $signature = 'marketing:process-automation-emails
        {--limit=50 : Maximum assignments+sends to process across all automations in this run}
        {--automation_id= : Process only a specific automation id}
        {--dry-run : Preview assignments and sends without writing anything}';

    protected $description = 'Assign and send automation emails synchronously, without queue workers.';

    public function handle(
        AutomationAssignmentService $assignmentService,
        MarketingEmailDispatchService $dispatchService,
        AutomationRunMarkerService $markerService
    ): int {
        $limit    = max(1, (int) $this->option('limit'));
        $dryRun   = (bool) $this->option('dry-run');
        $remaining = $limit;
        $summary  = $this->emptySummary($dryRun, $limit);

        $automations = Automation::query()
            ->where('status', 'active')
            ->with('promotions')
            ->when($this->option('automation_id'), fn ($q, $id) => $q->whereKey($id))
            ->orderBy('id')
            ->limit(50)
            ->get();

        $summary['automations_found'] = $automations->count();

        Log::info('Automation email processor started.', [
            'dry_run'        => $dryRun,
            'limit'          => $limit,
            'automation_id'  => $this->option('automation_id') ?: null,
            'automations_found' => $automations->count(),
        ]);

        foreach ($automations as $automation) {
            if ($remaining <= 0) {
                break;
            }

            $summary['automations_checked']++;

            try {
                $report = $dryRun
                    ? $this->dryRunAutomation($automation, $assignmentService, $dispatchService, $remaining)
                    : $this->processAutomation($automation, $assignmentService, $dispatchService, $remaining, $dryRun);

                $remaining -= max(0, (int) ($report['customers_checked'] ?? 0));

                $summary['assigned_count']         += (int) ($report['assigned_count'] ?? 0);
                $summary['sent_count']             += (int) ($report['sent_count'] ?? 0);
                $summary['reminder_flagged_count'] += (int) ($report['reminder_flagged_count'] ?? 0);
                $summary['reminder_sent_count']    += (int) ($report['reminder_sent_count'] ?? 0);
                $summary['skipped_count']          += (int) ($report['skipped_count'] ?? 0);
                $summary['errors_count']           += (int) ($report['errors_count'] ?? 0);
                $summary['automations'][]          = $report;
            } catch (Throwable $exception) {
                $summary['errors_count']++;
                $summary['automations'][] = $this->automationReport($automation, 'error', $exception->getMessage());
                Log::error('Automation processor exception.', [
                    'automation_id' => $automation->getKey(),
                    'message'       => $exception->getMessage(),
                ]);
            }
        }

        $summary['marker'] = $markerService->refresh();

        $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function dryRunAutomation(
        Automation $automation,
        AutomationAssignmentService $assignmentService,
        MarketingEmailDispatchService $dispatchService,
        int $limit
    ): array {
        $assignResult = $assignmentService->assign($automation, $limit, true);
        $sendResult   = $dispatchService->sendForAutomation($automation, $limit, true);

        return array_merge(
            $this->automationReport($automation, 'dry_run'),
            [
                'customers_checked'      => (int) ($assignResult['customers_checked'] ?? 0),
                'assigned_count'         => (int) ($assignResult['assigned_count'] ?? 0),
                'cooldown_skipped_count' => (int) ($assignResult['cooldown_skipped_count'] ?? 0),
                'already_assigned_count' => (int) ($assignResult['already_assigned_count'] ?? 0),
                'reminder_flagged_count' => (int) ($assignResult['reminder_flagged_count'] ?? 0),
                'cooldown_days'          => $assignResult['cooldown_days'] ?? 0,
                'sent_count'             => (int) ($sendResult['sent_count'] ?? 0),
                'reminder_sent_count'    => (int) ($sendResult['reminder_sent_count'] ?? 0),
                'skipped_count'          => (int) ($assignResult['skipped_count'] ?? 0) + (int) ($sendResult['skipped_count'] ?? 0),
                'errors_count'           => (int) ($assignResult['errors_count'] ?? 0) + (int) ($sendResult['errors_count'] ?? 0),
            ]
        );
    }

    private function processAutomation(
        Automation $automation,
        AutomationAssignmentService $assignmentService,
        MarketingEmailDispatchService $dispatchService,
        int $limit,
        bool $dryRun
    ): array {
        $assignResult = $assignmentService->assign($automation, $limit, false);

        Log::info('Automation assignments completed.', [
            'automation_id'          => $automation->getKey(),
            'trigger'                => $automation->trigger,
            'assigned_count'         => $assignResult['assigned_count'] ?? 0,
            'cooldown_skipped_count' => $assignResult['cooldown_skipped_count'] ?? 0,
            'already_assigned_count' => $assignResult['already_assigned_count'] ?? 0,
            'reminder_flagged_count' => $assignResult['reminder_flagged_count'] ?? 0,
            'skipped_count'          => $assignResult['skipped_count'] ?? 0,
        ]);

        $sendResult = $dispatchService->sendForAutomation($automation, $limit, false);

        $assignedCount      = (int) ($assignResult['assigned_count'] ?? 0);
        $sentCount          = (int) ($sendResult['sent_count'] ?? 0);
        $reminderSentCount  = (int) ($sendResult['reminder_sent_count'] ?? 0);

        if ($assignedCount > 0 || $sentCount > 0 || $reminderSentCount > 0) {
            $automation->increment('total_activation', $assignedCount);
            // total_sent include sia prime email sia reminder
            $automation->increment('total_sent', $sentCount + $reminderSentCount);
            $automation->forceFill(['last_run_at' => now()])->save();
        }

        Log::info('Automation emails dispatched.', [
            'automation_id'      => $automation->getKey(),
            'sent_count'         => $sentCount,
            'reminder_sent_count' => $reminderSentCount,
            'skipped_count'      => $sendResult['skipped_count'] ?? 0,
        ]);

        return array_merge(
            $this->automationReport($automation, 'write'),
            [
                'customers_checked'      => (int) ($assignResult['customers_checked'] ?? 0),
                'assigned_count'         => $assignedCount,
                'cooldown_skipped_count' => (int) ($assignResult['cooldown_skipped_count'] ?? 0),
                'already_assigned_count' => (int) ($assignResult['already_assigned_count'] ?? 0),
                'reminder_flagged_count' => (int) ($assignResult['reminder_flagged_count'] ?? 0),
                'cooldown_days'          => $assignResult['cooldown_days'] ?? 0,
                'sent_count'             => $sentCount,
                'reminder_sent_count'    => $reminderSentCount,
                'skipped_count'          => (int) ($assignResult['skipped_count'] ?? 0) + (int) ($sendResult['skipped_count'] ?? 0),
                'errors_count'           => (int) ($assignResult['errors_count'] ?? 0) + (int) ($sendResult['errors_count'] ?? 0),
                'assign_failure_reason'  => $assignResult['failure_reason'] ?? null,
            ]
        );
    }

    private function emptySummary(bool $dryRun, int $limit): array
    {
        return [
            'mode'                   => $dryRun ? 'dry_run' : 'write',
            'limit'                  => $limit,
            'automations_found'      => 0,
            'automations_checked'    => 0,
            'assigned_count'         => 0,
            'sent_count'             => 0,
            'reminder_flagged_count' => 0,
            'reminder_sent_count'    => 0,
            'skipped_count'          => 0,
            'errors_count'           => 0,
            'automations'            => [],
            'marker'                 => null,
        ];
    }

    private function automationReport(Automation $automation, string $mode, ?string $skipReason = null): array
    {
        return [
            'mode'          => $mode,
            'automation_id' => $automation->getKey(),
            'trigger'       => $automation->trigger,
            'status'        => $automation->status,
            'skip_reason'   => $skipReason,
        ];
    }
}
