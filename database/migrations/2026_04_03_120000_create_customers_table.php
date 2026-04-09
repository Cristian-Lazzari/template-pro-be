<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('surname', 50);
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('gender', 20)->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->json('profile_answers')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('marketing_consent_at')->nullable();
            $table->timestamp('profiling_consent_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
