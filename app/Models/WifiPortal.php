<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WifiPortal extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'survey_template_id',
        'name',
        'slug',
        'portal_token',
        'welcome_title',
        'welcome_text',
        'terms_text',
        'logo_override_path',
        'is_active',
        'require_marketing_consent',
        'session_ttl_minutes',
        'network_vendor',
        'post_login_redirect_url',
        'integration_key',
        'integration_secret',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_marketing_consent' => 'boolean',
        'settings' => 'array',
        'integration_secret' => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $portal): void {
            $portal->slug ??= Str::slug($portal->name.'-'.Str::random(4));
            $portal->portal_token ??= Str::random(48);
            $portal->integration_key ??= 'pk_'.Str::random(24);
            $portal->integration_secret ??= Str::random(48);
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function surveyTemplate(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WifiSession::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
