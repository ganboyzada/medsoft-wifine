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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->string('question_key');
            $table->string('label');
            $table->enum('type', [
                'short_text',
                'long_text',
                'single_choice',
                'multi_choice',
                'rating',
                'nps',
                'yes_no',
                'phone',
                'date',
            ]);
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order_index')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['survey_template_id', 'question_key']);
            $table->index(['survey_template_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
