<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Accès aux documents joints (PDF, Word…), réservé aux membres connectés.
 * Les fichiers sont hors du dossier public et ne sont jamais exécutés ni interprétés par le navigateur
 * comme une page du site.
 */
class AttachmentController extends Controller
{
    private const SAFE_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'Content-Security-Policy' => "sandbox; default-src 'none'; style-src 'unsafe-inline'",
        'Cache-Control' => 'private, max-age=3600',
    ];

    /** Téléchargement (tous les documents). */
    public function download(Attachment $attachment): BinaryFileResponse
    {
        abort_unless($attachment->kind === 'document', 404);
        $path = Storage::disk('local')->path($attachment->path);
        abort_unless(is_file($path), 404);

        return response()->download($path, $attachment->original_name, self::SAFE_HEADERS + [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /** Lecture dans le navigateur : uniquement les PDF. */
    public function view(Attachment $attachment): BinaryFileResponse
    {
        abort_unless($attachment->isPdf(), 404);
        $path = Storage::disk('local')->path($attachment->path);
        abort_unless(is_file($path), 404);

        return response()->file($path, self::SAFE_HEADERS + [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addcslashes($attachment->original_name, '"\\').'"',
        ]);
    }
}
