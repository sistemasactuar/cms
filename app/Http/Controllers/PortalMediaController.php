<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalMediaController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        abort_unless(str_starts_with($path, 'votaciones/'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}
