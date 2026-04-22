<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'username', 'bio', 'email', 'role', 'is_banned', 'banned_at', 'ban_reason', 'password', 'google_id', 'profile_photo_path', 'cover_image_path', 'social_links', 'topic_badges'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $appends = [
        'profile_photo_url',
        'cover_image_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'topic_badges' => 'array',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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

    public function commentVotes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function followingUsers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    public function followerUsers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    public function pinnedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'user_pinned_posts')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('user_pinned_posts.position');
    }

    public function contactMessagesResolved(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'resolved_by');
    }

    public function brainBotMessages(): HasMany
    {
        return $this->hasMany(BrainBotMessage::class);
    }

    public function postReports(): HasMany
    {
        return $this->hasMany(PostReport::class, 'reporter_id');
    }

    public function reviewedPostReports(): HasMany
    {
        return $this->hasMany(PostReport::class, 'reviewed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    public function isFollowing(?User $user): bool
    {
        if (! $user || $this->is($user)) {
            return false;
        }

        return $this->followingUsers->contains('id', $user->id);
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return asset('storage/'.$this->profile_photo_path);
        }

        $initials = collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_substr($part, 0, 1))
            ->implode('');

        $label = $initials !== '' ? strtoupper($initials) : strtoupper(mb_substr($this->name ?: 'U', 0, 1));

        return 'data:image/svg+xml;utf8,'.rawurlencode(sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" rx="100" fill="#0f172a"/><circle cx="100" cy="78" r="34" fill="#38bdf8"/><path d="M38 170c12-30 35-46 62-46s50 16 62 46" fill="#38bdf8"/><text x="100" y="112" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" font-weight="700" fill="#ffffff">%s</text></svg>',
            e($label)
        ));
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image_path) {
            return asset('storage/'.$this->cover_image_path);
        }

        return 'data:image/svg+xml;utf8,'.rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="520" viewBox="0 0 1600 520"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a"/><stop offset="100%" stop-color="#155e75"/></linearGradient></defs><rect width="1600" height="520" fill="url(#g)"/><circle cx="280" cy="120" r="140" fill="#22d3ee" fill-opacity="0.15"/><circle cx="1240" cy="430" r="190" fill="#a5f3fc" fill-opacity="0.12"/></svg>'
        );
    }

    public static function uniqueUsername(string $seed, ?int $ignoreId = null): string
    {
        $base = Str::slug($seed, '_');
        if ($base === '') {
            $base = 'user';
        }

        $base = Str::limit($base, 56, '');
        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $counter = 2;

        while (static::query()
            ->where('username', $username)
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $suffix = '_'.$counter;
            $username = Str::limit($base, max(1, 60 - strlen($suffix)), '').$suffix;
            $counter++;
        }

        return $username;
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (! $user->username) {
                $seed = trim((string) $user->name) !== '' ? (string) $user->name : (string) $user->email;
                $user->username = static::uniqueUsername($seed);
            }
        });
    }
}
