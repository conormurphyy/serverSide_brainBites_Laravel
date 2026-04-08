<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledPostFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_scheduled_public_post_is_hidden_from_guests(): void
    {
        $scheduledPost = $this->makePost(now()->addDay());

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertDontSee($scheduledPost->title);

        $this->get(route('posts.show', $scheduledPost))
            ->assertForbidden();
    }

    public function test_author_can_still_view_their_future_scheduled_post(): void
    {
        $author = User::factory()->create();
        $scheduledPost = $this->makePost(now()->addHours(2), $author);

        $this->actingAs($author)
            ->get(route('posts.show', $scheduledPost))
            ->assertOk()
            ->assertSee($scheduledPost->title);
    }

    private function makePost(\DateTimeInterface $publishedAt, ?User $author = null): Post
    {
        $owner = $author ?: User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Physics',
            'description' => 'Physics category',
        ]);

        return Post::query()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Scheduled publishing behavior test title',
            'summary' => 'This summary is long enough for a valid post and helps verify schedule filtering.',
            'body' => str_repeat('Scheduled post body content for feature testing. ', 4),
            'image_path' => 'embedded',
            'image_mime' => 'image/png',
            'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/a6sAAAAASUVORK5CYII=',
            'is_public' => true,
            'published_at' => $publishedAt,
        ]);
    }
}
