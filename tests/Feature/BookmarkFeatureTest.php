<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_toggle_bookmark_for_post(): void
    {
        $user = User::factory()->create();
        $post = $this->makePublicPost();

        $this->actingAs($user)
            ->post(route('posts.bookmark', $post))
            ->assertRedirect();

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user)
            ->post(route('posts.bookmark', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('bookmarks', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_bookmarks_page_lists_saved_posts_for_signed_in_user(): void
    {
        $user = User::factory()->create();
        $savedPost = $this->makePublicPost('Saved answer title');

        $this->actingAs($user)->post(route('posts.bookmark', $savedPost));

        $this->actingAs($user)
            ->get(route('bookmarks.index'))
            ->assertOk()
            ->assertSee('Saved answer title');
    }

    private function makePublicPost(string $title = 'How does this bookmarked post work?'): Post
    {
        $author = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Testing',
            'description' => 'Testing category',
        ]);

        return Post::query()->create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => $title,
            'summary' => 'This is a short summary long enough to pass request and model constraints.',
            'body' => str_repeat('Detailed body content for bookmark testing. ', 4),
            'image_path' => 'embedded',
            'image_mime' => 'image/png',
            'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/a6sAAAAASUVORK5CYII=',
            'is_public' => true,
            'published_at' => now()->subHour(),
        ]);
    }
}
