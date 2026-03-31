<?php

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;

class AllergensFromConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configAllergens = config('configurazione.allergens', []);

        if (!is_array($configAllergens) || $configAllergens === []) {
            $this->command?->warn('config(configurazione.allergens) vuoto o non valido.');

            return;
        }

        foreach ($configAllergens as $id => $allergenData) {
            if (!is_numeric($id) || !is_array($allergenData)) {
                continue;
            }

            $allergen = Allergen::query()->updateOrCreate(
                ['id' => (int) $id],
                [
                    'special' => (int) ($allergenData['special'] ?? 0),
                    'img' => $allergenData['img'] ?? null,
                ]
            );

            $names = $allergenData['name'] ?? null;

            if (is_array($names)) {
                foreach ($names as $lang => $name) {
                    if (!is_string($lang) || !is_string($name) || trim($lang) === '' || trim($name) === '') {
                        continue;
                    }

                    $allergen->translations()->updateOrCreate(
                        ['lang' => trim($lang)],
                        ['name' => trim($name)]
                    );
                }

                continue;
            }

            if (is_string($names) && trim($names) !== '') {
                $allergen->translations()->updateOrCreate(
                    ['lang' => 'it'],
                    ['name' => trim($names)]
                );
            }
        }
    }
}
