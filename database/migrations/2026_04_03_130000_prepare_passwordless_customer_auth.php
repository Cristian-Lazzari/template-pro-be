<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'password')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE customers MODIFY password VARCHAR(255) NULL');
            }
        }

        if (!Schema::hasTable('email_otps')) {
            Schema::create('email_otps', function (Blueprint $table) {
                $table->id();
                $table->string('email', 100)->index();
                $table->string('code');
                $table->timestamp('expires_at')->index();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('email_otps')) {
            Schema::drop('email_otps');
        }
    }
};
