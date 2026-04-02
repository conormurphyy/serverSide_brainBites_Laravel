<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $pixelPngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/a6sAAAAASUVORK5CYII=';

        $contributor = User::factory()->create([
            'name' => 'Lead Chemist',
            'email' => 'chemist@brainbites.test',
            'role' => 'contributor',
        ]);

        User::factory()->create([
            'name' => 'BrainBites Admin',
            'email' => 'admin@brainbites.test',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Curious Reader',
            'email' => 'reader@brainbites.test',
            'role' => 'reader',
        ]);

        $categories = collect([
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Computing, software, and digital systems.'],
            ['name' => 'Biology', 'slug' => 'biology', 'description' => 'Living organisms, ecosystems, and life processes.'],
            ['name' => 'Chemistry', 'slug' => 'chemistry', 'description' => 'Matter, reactions, and molecules in everyday life.'],
            ['name' => 'Physics', 'slug' => 'physics', 'description' => 'Forces, motion, energy, and how the universe behaves.'],
        ])->map(fn (array $category) => Category::query()->create($category));

        Post::query()->create([
            'user_id' => $contributor->id,
            'category_id' => $categories->firstWhere('name', 'Technology')->id,
            'title' => 'How was the first programming language created?',
            'slug' => 'how-was-the-first-programming-language-created',
            'summary' => 'Before modern tools existed, pioneers designed symbolic systems by hand and translated them to machine instructions.',
            'body' => 'The first programming languages were built by mathematically defining symbolic instructions that could be converted into machine code. Early programmers wrote translators called assemblers and compilers, often directly in machine language. Over time, each generation of tools made the next generation easier to build, leading to the ecosystems we use today.',
            'image_path' => 'embedded',
            'image_mime' => 'image/png',
            'image_base64' => $pixelPngBase64,
            'is_public' => true,
            'published_at' => now()->subDays(3),
        ]);

        Post::query()->create([
            'user_id' => $contributor->id,
            'category_id' => $categories->firstWhere('name', 'Physics')->id,
            'title' => 'Why is the sky blue during the day?',
            'slug' => 'why-is-the-sky-blue-during-the-day',
            'summary' => 'Air molecules scatter blue wavelengths of sunlight more than red wavelengths, making blue light dominant in our view.',
            'body' => 'Sunlight contains many colors. As sunlight passes through Earth\'s atmosphere, gas molecules scatter shorter wavelengths of light more efficiently. Blue and violet wavelengths scatter most, but our eyes are more sensitive to blue and some violet gets absorbed in the upper atmosphere. The result is a sky that appears blue in daylight.',
            'image_path' => 'embedded',
            'image_mime' => 'image/png',
            'image_base64' => $pixelPngBase64,
            'is_public' => true,
            'published_at' => now()->subDays(2),
        ]);

        Post::query()->create([
            'user_id' => $contributor->id,
            'category_id' => $categories->firstWhere('name', 'Biology')->id,
            'title' => 'How do plants know which way to grow?',
            'slug' => 'how-do-plants-know-which-way-to-grow',
            'summary' => 'Plants respond to light and gravity through chemical signals that control cell expansion direction.',
            'body' => 'Plants use tropisms to orient their growth. In phototropism, plant hormones like auxin accumulate on the darker side of a stem and cause those cells to elongate more, bending the plant toward light. In gravitropism, roots and shoots detect gravity and distribute growth hormones differently, helping roots grow downward and shoots upward.',
            'image_path' => 'embedded',
            'image_mime' => 'image/png',
            'image_base64' => $pixelPngBase64,
            'is_public' => true,
            'published_at' => now()->subDay(),
        ]);
    }
}
