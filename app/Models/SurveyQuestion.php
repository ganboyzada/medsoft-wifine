<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_template_id',
        'question_key',
        'label',
        'type',
        'placeholder',
        'options',
        'is_required',
        'order_index',
        'settings',
    ];

    protected $casts = [
        'options' => 'array',
        'settings' => 'array',
        'is_required' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $question): void {
            $question->question_key ??= 'q_'.Str::lower(Str::random(10));
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
