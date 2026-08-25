<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_age_bands', function (Blueprint $table) {
            $table->foreignId('content_item_id')->constrained()->cascadeOnDelete();
            $table->string('age_band');
            $table->timestamps();

            $table->primary(['content_item_id', 'age_band']);
            $table->index('age_band');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_age_bands');
    }
};
