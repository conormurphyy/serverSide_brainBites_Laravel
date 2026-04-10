<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'summary',
        'body',
        'image_path',
        'image_mime',
        'image_base64',
        'is_public',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)
            ->where(function (Builder $nested): void {
                $nested->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getImageSourceAttribute(): string
    {
        if ($this->image_base64 && $this->image_mime) {
            return 'data:'.$this->image_mime.';base64,'.$this->image_base64;
        }

        if ($this->image_path && str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        // Visible fallback prevents blank cards when legacy rows have no image data.
        return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='1200' height='630'><rect width='100%' height='100%' fill='%231f2937'/><text x='60' y='320' fill='white' font-size='56' font-family='Arial'>No Image Available</text></svg>";
    }

    public function getReadingTimeMinutesAttribute(): int
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($this->body ?? '')) ?? '');
        $wordCount = str_word_count($text);

        if ($wordCount <= 0) {
            return 1;
        }

        return max(1, (int) ceil($wordCount / 200));
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        $palette = [
            'technology' => 'bb-chip bb-chip-technology',
            'science' => 'bb-chip bb-chip-science',
            'health' => 'bb-chip bb-chip-health',
            'finance' => 'bb-chip bb-chip-finance',
            'education' => 'bb-chip bb-chip-education',
        ];

        return $palette[$this->category->slug ?? ''] ?? 'bb-chip';
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes->contains('user_id', $user->id);
    }

    public function isBookmarkedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->bookmarks->contains('user_id', $user->id);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if (! $post->slug || $post->isDirty('title')) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }
        });
    }
}
