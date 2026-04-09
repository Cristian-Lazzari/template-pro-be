<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $property = json_encode([
            'marketing_consent_text' => 'Vuoi ricevere novita, eventi e offerte del ristorante via email?',
            'profiling_consent_text' => 'Vuoi ricevere promozioni personalizzate in base ai tuoi gusti e alle tue preferenze?',
            'questions' => [],
        ]);

        $existing = DB::table('settings')->where('name', 'customer_profile')->first();

        if ($existing) {
            $current = json_decode($existing->property ?? '[]', true);
            if (!is_array($current)) {
                $current = [];
            }

            DB::table('settings')
                ->where('id', $existing->id)
                ->update([
                    'property' => json_encode(array_replace_recursive(json_decode($property, true), $current)),
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('settings')->insert([
            'name' => 'customer_profile',
            'status' => 1,
            'property' => $property,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('settings')->where('name', 'customer_profile')->delete();
    }
};
