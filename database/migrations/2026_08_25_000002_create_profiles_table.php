<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('country_code', 2)->nullable();
            $table->string('language_code', 5)->nullable();
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamps();

            $table->index('country_code');
            $table->index('language_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
