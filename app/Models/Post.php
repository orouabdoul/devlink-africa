<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    public const TYPES = [
        'question' => 'Question',
        'discussion' => 'Discussion',
        'resource' => 'Ressource',
    ];

    protected $fillable = ['category_id', 'title', 'content', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /** Chargement standard pour les listes : auteur, catégorie, compteurs, like de l'utilisateur courant. */
    public function scopeForFeed(Builder $query, ?User $viewer): Builder
    {
        return $query
            ->with(['user:id,name', 'user.profile:id,user_id,avatar,country,headline', 'category'])
            ->withCount(['comments', 'likes'])
            ->when($viewer, fn ($q) => $q->withExists([
                'likes as liked' => fn ($l) => $l->where('user_id', $viewer->id),
            ]));
    }

    /** Recherche par mot-clé dans le titre et le contenu (compatible MySQL et SQLite). */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $words = collect(preg_split('/\s+/', $term))->filter()->take(6);

        return $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $word).'%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('content', 'like', $like));
            }
        });
    }

    public function getExcerptAttribute(): string
    {
        $plain = preg_replace('/```.*?```/s', ' [code] ', $this->content);
        $plain = strip_tags(Str::markdown($plain, ['html_input' => 'strip']));

        return Str::limit(trim(preg_replace('/\s+/', ' ', $plain)), 220);
    }

    public function getContentHtmlAttribute(): string
    {
        return self::renderMarkdown($this->content);
    }

    /** Markdown sécurisé : HTML brut supprimé, liens dangereux interdits. */
    public static function renderMarkdown(string $text): string
    {
        return Str::markdown($text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);
    }
}
