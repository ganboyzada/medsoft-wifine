<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wifi_portal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wifi_session_id')->constrained()->cascadeOnDelete();
            $table->decimal('sentiment_score', 5, 2)->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['organization_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
