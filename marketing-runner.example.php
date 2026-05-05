<?php

// --------------------------------------------------------------------------
// Marketing shared hosting runner example
// --------------------------------------------------------------------------
//
// Copy this file manually to:
//
//   /home/USER/marketing-runner.php
//
// Then configure ONE cPanel cron, for example:
//
//   */5 * * * * /usr/local/bin/php /home/USER/marketing-runner.php >> /home/USER/marketing-runner.log 2>&1
//
// This runner checks each Laravel installation marker file:
//
//   storage/app/marketing/next-run.json
//
// It starts Laravel only for sites with pending campaigns due now.
// It does not need a queue worker for the shared hosting batch mode.

$phpBinary = '/usr/local/bin/php';
$limitPerSite = 20;
$maxSitesPerRun = 5;

$sites = [
    '/home/USER/cliente1',
    '/home/USER/cliente2',
    '/home/USER/cliente3',
];

$lockFile = '/tmp/marketing-runner.lock';
$lockTtlSeconds = 600;

logLine('Runner started.');

if (file_exists($lockFile) && (time() - filemtime($lockFile)) < $lockTtlSeconds) {
    logLine('Skipped: lock file is still fresh.');
    exit(0);
}

$lockHandle = fopen($lockFile, 'c');

if ($lockHandle === false) {
    logLine('Error: unable to open lock file.');
    exit(1);
}

if (! flock($lockHandle, LOCK_EX | LOCK_NB)) {
    logLine('Skipped: another runner process is active.');
    exit(0);
}

ftruncate($lockHandle, 0);
fwrite($lockHandle, json_encode([
    'pid' => getmypid(),
    'started_at' => date(DATE_ATOM),
]));

register_shutdown_function(function () use ($lockHandle, $lockFile): void {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);

    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
});

$processedSites = 0;
$now = new DateTimeImmutable('now');

foreach ($sites as $sitePath) {
    $sitePath = rtrim((string) $sitePath, '/');

    if ($processedSites >= $maxSitesPerRun) {
        logLine('Max sites per run reached.');
        break;
    }

    if ($sitePath === '' || ! is_dir($sitePath)) {
        logLine("Skipped: invalid site path [{$sitePath}].");
        continue;
    }

    $artisanPath = $sitePath . '/artisan';

    if (! is_file($artisanPath)) {
        logLine("Skipped: artisan not found [{$sitePath}].");
        continue;
    }

    $markerPath = $sitePath . '/storage/app/marketing/next-run.json';

    if (! is_file($markerPath)) {
        logLine("Skipped: marker not found [{$sitePath}].");
        continue;
    }

    $marker = json_decode((string) file_get_contents($markerPath), true);

    if (! is_array($marker) || json_last_error() !== JSON_ERROR_NONE) {
        logLine("Skipped: invalid marker JSON [{$sitePath}].");
        continue;
    }

    if (($marker['has_pending_campaigns'] ?? false) !== true) {
        logLine("Skipped: no pending campaigns [{$sitePath}].");
        continue;
    }

    $nextDueAtValue = $marker['next_due_at'] ?? null;

    if (! is_string($nextDueAtValue) || trim($nextDueAtValue) === '') {
        logLine("Skipped: missing next_due_at [{$sitePath}].");
        continue;
    }

    try {
        $nextDueAt = new DateTimeImmutable($nextDueAtValue);
    } catch (Throwable $exception) {
        logLine("Skipped: invalid next_due_at [{$sitePath}] {$exception->getMessage()}.");
        continue;
    }

    if ($nextDueAt > $now) {
        logLine("Skipped: next run is in the future [{$sitePath}] {$nextDueAt->format(DATE_ATOM)}.");
        continue;
    }

    $processedSites++;
    logLine("Processing site [{$sitePath}].");

    $command = sprintf(
        'cd %s && %s artisan marketing:process-scheduled-campaign-emails --limit=%d --no-interaction',
        escapeshellarg($sitePath),
        escapeshellcmd($phpBinary),
        (int) $limitPerSite
    );

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    logLine("Processed site [{$sitePath}] exit_code={$exitCode}.");

    if ($output !== []) {
        logLine("Command output [{$sitePath}]:");

        foreach ($output as $line) {
            logLine('  ' . $line);
        }
    }
}

logLine("Runner completed. processed_sites={$processedSites}.");

function logLine(string $message): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}
