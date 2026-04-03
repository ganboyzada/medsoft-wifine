<?php

namespace App\Support;

class PublicMediaUrl
{
    public static function fromPublicDiskPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $publicStoragePath = public_path('storage/'.$normalized);

        if (file_exists($publicStoragePath)) {
            return asset('storage/'.$normalized);
        }

        return route('media.public', ['path' => $normalized]);
    }
}
