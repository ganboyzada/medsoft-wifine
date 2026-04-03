<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    public const LANGUAGE_AZERBAIJANI = 'az';
    public const LANGUAGE_ENGLISH = 'en';

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'contact_email',
        'contact_phone',
        'timezone',
        'default_language',
        'logo_path',
        'primary_color',
        'accent_color',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $organization): void {
            if ($organization->slug === null) {
                $organization->slug = Str::slug($organization->name.'-'.Str::random(5));
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function portals(): HasMany
    {
        return $this->hasMany(WifiPortal::class);
    }

    public function surveyTemplates(): HasMany
    {
        return $this->hasMany(SurveyTemplate::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WifiSession::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public static function supportedLanguages(): array
    {
        return [
            self::LANGUAGE_AZERBAIJANI,
            self::LANGUAGE_ENGLISH,
        ];
    }
}
