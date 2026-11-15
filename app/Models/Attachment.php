<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Attachment extends Model
{
    public const MAX_PER_POST = 4;

    public const MAX_PER_COMMENT = 2;

    public const MAX_DOCUMENTS_PER_POST = 3;

    public const MAX_DOCUMENTS_PER_COMMENT = 1;

    /** Formats de documents acceptés (vérifiés sur le contenu réel ET sur l'extension). */
    public const DOCUMENT_MIMES = 'pdf,docx,pptx,xlsx,odt,odp,ods,txt,csv,json';

    public const DOCUMENT_EXTENSIONS = 'pdf,docx,pptx,xlsx,odt,odp,ods,txt,md,csv,json,log';

    protected $fillable = ['user_id', 'post_id', 'comment_id', 'kind', 'path', 'original_name', 'mime', 'size', 'width', 'height'];

    protected $hidden = ['path', 'user_id', 'post_id', 'comment_id', 'mime', 'created_at', 'updated_at'];

    protected $appends = ['url', 'extension'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        // Les documents ne sont jamais servis directement : ils passent par un contrôleur (membres connectés).
        return $this->kind === 'document'
            ? route('attachments.download', $this)
            : asset('storage/'.$this->path);
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name ?: $this->path, PATHINFO_EXTENSION));
    }

    public function isPdf(): bool
    {
        return $this->kind === 'document' && $this->mime === 'application/pdf';
    }

    /**
     * Enregistre des documents dans le stockage PRIVÉ (hors du dossier public).
     *
     * @param  array<UploadedFile>  $files
     * @param  array{post_id?: int, comment_id?: int}  $owner
     */
    public static function storeDocuments(array $files, User $user, array $owner): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $ext = strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs('documents/'.now()->format('Y/m'), Str::uuid().'.'.$ext, 'local');

            static::create([
                ...$owner,
                'user_id' => $user->id,
                'kind' => 'document',
                'path' => $path,
                'original_name' => static::cleanName($file->getClientOriginalName()),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /** Nom affiché et proposé au téléchargement : sans chemin, sans caractères de contrôle. */
    public static function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F"<>:|?*\/\\\\]+/u', '_', $name);
        $name = trim($name, ". \t") ?: 'document';

        return Str::limit($name, 150, '');
    }

    /**
     * Enregistre des captures. Le nom et l'extension viennent du contenu réel du fichier,
     * jamais du nom fourni par l'utilisateur.
     *
     * @param  array<UploadedFile>  $files
     * @param  array{post_id?: int, comment_id?: int}  $owner
     */
    public static function storeImages(array $files, User $user, array $owner): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $ext = match ($file->getMimeType()) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $path = $file->storeAs('attachments/'.now()->format('Y/m'), Str::uuid().'.'.$ext, 'public');
            [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

            static::create([
                ...$owner,
                'user_id' => $user->id,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $width,
                'height' => $height,
            ]);
        }
    }

    /** Supprime du disque les fichiers des pièces jointes visées (les lignes sont supprimées par la base). */
    public static function purgeFiles(Builder $query): void
    {
        $query->get(['path', 'kind'])
            ->groupBy('kind')
            ->each(fn ($items, $kind) => Storage::disk($kind === 'document' ? 'local' : 'public')->delete($items->pluck('path')->all()));
    }
}
