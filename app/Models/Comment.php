<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ['content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->orderBy('id');
    }

    protected static function booted(): void
    {
        static::deleting(fn (Comment $comment) => Attachment::purgeFiles(Attachment::where('comment_id', $comment->id)));
    }

    public function getContentHtmlAttribute(): string
    {
        return Post::renderMarkdown($this->content);
    }
}
