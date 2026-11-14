<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'headline',
        'country',
        'avatar',
        'bio',
        'skills',
        'github_url',
        'linkedin_url',
    ];

    protected $appends = ['avatar_url'];

    protected $hidden = ['avatar'];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        // asset() suit l'adresse réellement utilisée (127.0.0.1:8000, localhost/..., domaine en ligne)
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /** Pourcentage de complétion du profil, affiché sur le tableau de bord. */
    public function completion(): int
    {
        $fields = ['headline', 'country', 'avatar', 'bio', 'skills', 'github_url'];
        $filled = collect($fields)->filter(fn ($f) => ! empty($this->{$f}))->count();

        return (int) round($filled / count($fields) * 100);
    }
}
