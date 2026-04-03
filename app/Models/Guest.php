<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'phone',
        'phone_normalized',
        'first_name',
        'gender',
        'consent_marketing',
        'consent_terms',
        'first_seen_at',
        'last_seen_at',
        'metadata',
    ];

    protected $casts = [
        'consent_marketing' => 'boolean',
        'consent_terms' => 'boolean',
        'metadata' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WifiSession::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function touchSeenAt(?Carbon $at = null): void
    {
        $time = $at ?? now();
        $this->first_seen_at ??= $time;
        $this->last_seen_at = $time;
    }
}
