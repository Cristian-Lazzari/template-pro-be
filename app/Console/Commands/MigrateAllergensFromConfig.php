<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAllergensFromConfig extends Command
{
    protected $signature = 'allergens:migrate-from-config {--dry-run : Non scrive nulla, mostra solo cosa farebbe}';
    protected $description = 'Popola allergens/allergen_translations dal config e migra gli allergen ids dai campi JSON (products.allergens, ingredients.allergens) alle pivot';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $configAllergens = config('configurazione.allergens');

        if (!is_array($configAllergens) || !count($configAllergens)) {
            $this->error('config(configurazione.allergens) vuoto o non valido.');
            return self::FAILURE;
        }

        $this->info('Step 1) Upsert allergens + allergen_translations (it) dal config...');
        $this->upsertAllergensFromConfig($configAllergens, $dryRun);

        $this->info('Step 2) Migrazione products.allergens -> product_allergen ...');
        $this->migrateModelAllergensToPivot(
            table: 'products',
            idColumn: 'id',
            jsonColumn: 'allergens',
            pivotTable: 'product_allergen',
            pivotFkColumn: 'product_id',
            dryRun: $dryRun
        );

        $this->info('Step 3) Migrazione ingredients.allergens -> ingredient_allergen ...');
        $this->migrateModelAllergensToPivot(
            table: 'ingredients',
            idColumn: 'id',
            jsonColumn: 'allergens',
            pivotTable: 'ingredient_allergen',
            pivotFkColumn: 'ingredient_id',
            dryRun: $dryRun
        );

        $this->info('OK ✅');
        if ($dryRun) {
            $this->warn('Dry-run attivo: nessuna scrittura effettuata.');
        }

        return self::SUCCESS;
    }

    private function upsertAllergensFromConfig(array $configAllergens, bool $dryRun): void
    {
        $countAllergens = 0;
        $countTranslations = 0;

        foreach ($configAllergens as $id => $a) {
            if (!is_numeric($id)) {
                continue;
            }

            $id = (int) $id;

            $special = isset($a['special']) ? (int) $a['special'] : 0;
            $img = $a['img'] ?? null;
            $names = $a['name'] ?? null;

            if (!$dryRun) {
                // manteniamo gli stessi ID del config
                DB::table('allergens')->updateOrInsert(
                    ['id' => $id],
                    [
                        'special' => $special,
                        'img' => $img,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }

            $countAllergens++;

            // gestione traduzioni
            if (is_array($names)) {

                foreach ($names as $lang => $name) {

                    if (!$name) {
                        continue;
                    }

                    if (!$dryRun) {
                        DB::table('allergen_translations')->updateOrInsert(
                            [
                                'allergen_id' => $id,
                                'lang' => $lang
                            ],
                            [
                                'name' => $name,
                                'updated_at' => now(),
                                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                            ]
                        );
                    }

                    $countTranslations++;
                }

            } else {

                // fallback compatibilità vecchio config (stringa)
                if ($names) {

                    if (!$dryRun) {
                        DB::table('allergen_translations')->updateOrInsert(
                            [
                                'allergen_id' => $id,
                                'lang' => 'it'
                            ],
                            [
                                'name' => $names,
                                'updated_at' => now(),
                                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                            ]
                        );
                    }

                    $countTranslations++;
                }

            }
        }

        $this->line(" - Allergens upsert: {$countAllergens}");
        $this->line(" - Translations upsert: {$countTranslations}");
    }

    private function migrateModelAllergensToPivot(
        string $table,
        string $idColumn,
        string $jsonColumn,
        string $pivotTable,
        string $pivotFkColumn,
        bool $dryRun
    ): void {
        // Leggiamo a chunk per non esplodere RAM
        $chunkSize = 500;

        $totalRows = DB::table($table)->count();
        $this->line(" - Righe {$table}: {$totalRows}");

        $inserted = 0;
        $skippedEmpty = 0;
        $skippedInvalid = 0;

        DB::table($table)
            ->select([$idColumn, $jsonColumn])
            ->orderBy($idColumn)
            ->chunk($chunkSize, function ($rows) use (
                $table, $idColumn, $jsonColumn, $pivotTable, $pivotFkColumn, $dryRun,
                &$inserted, &$skippedEmpty, &$skippedInvalid
            ) {
                foreach ($rows as $row) {
                    $modelId = $row->{$idColumn};
                    $raw = $row->{$jsonColumn} ?? null;

                    if (!$raw || trim((string)$raw) === '') {
                        $skippedEmpty++;
                        continue;
                    }

                    $ids = $this->parseAllergenIds($raw);

                    if ($ids === null) {
                        $skippedInvalid++;
                        continue;
                    }

                    // dedup
                    $ids = array_values(array_unique($ids));

                    foreach ($ids as $allergenId) {
                        if (!$dryRun) {
                            DB::table($pivotTable)->updateOrInsert(
                                [
                                    $pivotFkColumn => $modelId,
                                    'allergen_id' => $allergenId,
                                ],
                                []
                            );
                        }
                        $inserted++;
                    }
                }
            });

        $this->line(" - Inserimenti pivot {$pivotTable}: ~{$inserted}");
        $this->line(" - Skip vuoti: {$skippedEmpty}");
        $this->line(" - Skip invalidi: {$skippedInvalid}");
    }

    /**
     * Accetta sia JSON "[1,2,3]" che stringhe "1,2,3" (nel caso ti trovi roba sporca)
     */
    private function parseAllergenIds(string $raw): ?array
    {
        $raw = trim($raw);

        // Prova JSON
        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return null;
            }
            $out = [];
            foreach ($decoded as $v) {
                if (is_numeric($v)) $out[] = (int) $v;
            }
            return $out;
        }

        // Fallback: "1,2,3"
        $parts = array_map('trim', explode(',', $raw));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            if (!is_numeric($p)) return null;
            $out[] = (int) $p;
        }
        return $out;
    }
}