<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use Illuminate\Support\Facades\File;

class AutomationRunMarkerService
{
    public function refresh(): array
    {
        $activeCount = Automation::query()->where('status', 'active')->count();

        $data = [
            'has_active_automations' => $activeCount > 0,
            'active_count'           => $activeCount,
            'generated_at'           => now()->toIso8601String(),
        ];

        $this->write($data);

        return $data;
    }

    public function write(array $data): void
    {
        File::ensureDirectoryExists(dirname($this->path()));

        File::put(
            $this->path(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function path(): string
    {
        return storage_path('app/marketing/automation-next-run.json');
    }
}
