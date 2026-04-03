<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function publicDisk(Request $request, string $path): BinaryFileResponse
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        abort_if(
            $normalized === '' || str_contains($normalized, '..'),
            404
        );

        abort_unless(Storage::disk('public')->exists($normalized), 404);

        $absolutePath = Storage::disk('public')->path($normalized);
        $mimeType = Storage::disk('public')->mimeType($normalized) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
