<?php

use App\Enums\ModerationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('youtube');
            $table->string('external_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('embed_url');
            $table->integer('duration_seconds')->nullable();
            $table->string('original_language_code', 5)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('moderation_status', 20)->default(ModerationStatus::Candidate->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->decimal('editorial_score', 3, 2)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index('moderation_status');
            $table->index('published_at');
            $table->index('country_code');
            $table->index('original_language_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
