<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $property = json_encode([
            'code' => 'EUR',
            'symbol' => '€',
            'label' => 'Euro',
            'decimals' => 2,
        ]);

        $existing = DB::table('settings')->where('name', 'Valuta')->first();

        if ($existing) {
            $current = json_decode($existing->property ?? '[]', true);

            if (!is_array($current)) {
                $current = [];
            }

            DB::table('settings')
                ->where('id', $existing->id)
                ->update([
                    'status' => $existing->status ?? 1,
                    'property' => json_encode(array_replace_recursive(json_decode($property, true), $current)),
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('settings')->insert([
            'name' => 'Valuta',
            'status' => 1,
            'property' => $property,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('settings')->where('name', 'Valuta')->delete();
    }
};
