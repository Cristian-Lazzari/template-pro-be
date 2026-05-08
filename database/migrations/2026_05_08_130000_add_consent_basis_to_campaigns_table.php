<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaigns')) {
            return;
        }

        if (! Schema::hasColumn('campaigns', 'channel')) {
            Schema::table('campaigns', function (Blueprint $table) {
                $table->string('channel', 30)
                    ->default('email')
                    ->after('status');
            });
        }

        if (! Schema::hasColumn('campaigns', 'consent_basis')) {
            $afterColumn = Schema::hasColumn('campaigns', 'channel') ? 'channel' : 'status';

            Schema::table('campaigns', function (Blueprint $table) use ($afterColumn) {
                $table->string('consent_basis', 50)
                    ->default('explicit_email_marketing')
                    ->after($afterColumn);
            });
        }

        if (Schema::hasColumn('campaigns', 'channel')) {
            DB::table('campaigns')
                ->where(function ($query) {
                    $query->whereNull('channel')
                        ->orWhere('channel', '');
                })
                ->update(['channel' => 'email']);
        }

        if (Schema::hasColumn('campaigns', 'consent_basis')) {
            DB::table('campaigns')
                ->where(function ($query) {
                    $query->whereNull('consent_basis')
                        ->orWhere('consent_basis', '');
                })
                ->update(['consent_basis' => 'explicit_email_marketing']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaigns')) {
            return;
        }

        Schema::table('campaigns', function (Blueprint $table) {
            foreach (['consent_basis', 'channel'] as $column) {
                if (Schema::hasColumn('campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
