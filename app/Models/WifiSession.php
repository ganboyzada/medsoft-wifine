<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class WifiSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'wifi_portal_id',
        'guest_id',
        'session_token',
        'status',
        'client_mac',
        'ap_mac',
        'ip_address',
        'user_agent',
        'redirect_url',
        'metadata',
        'started_at',
        'survey_submitted_at',
        'authorized_at',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'survey_submitted_at' => 'datetime',
        'authorized_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            $session->session_token ??= (string) Str::uuid();
            $session->started_at ??= now();
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function portal(): BelongsTo
    {
        return $this->belongsTo(WifiPortal::class, 'wifi_portal_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function response(): HasOne
    {
        return $this->hasOne(SurveyResponse::class);
    }

    public function markAuthorized(): void
    {
        $this->status = 'authorized';
        $this->authorized_at = now();
    }
}
