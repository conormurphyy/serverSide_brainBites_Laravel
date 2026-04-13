<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'parent_comment_id',
        'body',
        'image_path',
        'voice_note_path',
        'voice_note_duration',
    ];

    protected $casts = [
        'voice_note_duration' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_votes')
            ->withTimestamps();
    }

    public function isUpvotedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (array_key_exists('is_upvoted_by_auth', $this->attributes)) {
            return (bool) $this->attributes['is_upvoted_by_auth'];
        }

        return $this->votes->contains('user_id', $user->id);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function getVoiceNoteUrlAttribute(): ?string
    {
        if (! $this->voice_note_path) {
            return null;
        }

        return Storage::disk('public')->url($this->voice_note_path);
    }
}