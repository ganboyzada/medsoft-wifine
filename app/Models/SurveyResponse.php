<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'wifi_portal_id',
        'survey_template_id',
        'guest_id',
        'wifi_session_id',
        'sentiment_score',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function portal(): BelongsTo
    {
        return $this->belongsTo(WifiPortal::class, 'wifi_portal_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WifiSession::class, 'wifi_session_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
