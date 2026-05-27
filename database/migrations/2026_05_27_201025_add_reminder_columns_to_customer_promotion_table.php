<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_promotion', function (Blueprint $table) {
            // Valorizzato da AutomationAssignmentService quando il cooldown scade
            // ma la promozione esiste ancora aperta/non usata.
            // Segnala che questa CustomerPromotion è candidata a ricevere un reminder.
            if (! Schema::hasColumn('customer_promotion', 'reminder_eligible_at')) {
                $table->timestamp('reminder_eligible_at')
                    ->nullable()
                    ->after('email_sent_at');
            }

            // Valorizzato da MarketingEmailDispatchService dopo l'invio del reminder.
            // Se null: reminder non ancora inviato (o non applicabile).
            // Se valorizzato: reminder già inviato — non riinviare.
            if (! Schema::hasColumn('customer_promotion', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')
                    ->nullable()
                    ->after('reminder_eligible_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_promotion', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('customer_promotion', 'reminder_eligible_at') ? 'reminder_eligible_at' : null,
                Schema::hasColumn('customer_promotion', 'reminder_sent_at')     ? 'reminder_sent_at'     : null,
            ]);

            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
