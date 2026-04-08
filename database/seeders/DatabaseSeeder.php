<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $contributor = User::query()->updateOrCreate([
            'email' => 'chemist@brainbites.test',
        ], [
            'name' => 'Lead Chemist',
            'role' => 'contributor',
            'password' => Hash::make('password'),
        ]);

        User::query()->updateOrCreate([
            'email' => 'admin@brainbites.test',
        ], [
            'name' => 'BrainBites Admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::query()->updateOrCreate([
            'email' => 'reader@brainbites.test',
        ], [
            'name' => 'Curious Reader',
            'role' => 'reader',
            'password' => Hash::make('password'),
        ]);

        $categories = collect([
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Computing, software, and digital systems.'],
            ['name' => 'Biology', 'slug' => 'biology', 'description' => 'Living organisms, ecosystems, and life processes.'],
            ['name' => 'Chemistry', 'slug' => 'chemistry', 'description' => 'Matter, reactions, and molecules in everyday life.'],
            ['name' => 'Physics', 'slug' => 'physics', 'description' => 'Forces, motion, energy, and how the universe behaves.'],
        ])->map(function (array $category): Category {
            return Category::query()->updateOrCreate([
                'slug' => $category['slug'],
            ], $category);
        });

        foreach ($this->samplePosts() as $post) {
            Post::query()->updateOrCreate([
                'slug' => $post['slug'],
            ], [
                'user_id' => $contributor->id,
                'category_id' => $categories->firstWhere('slug', $post['category_slug'])->id,
                'title' => $post['title'],
                'summary' => $post['summary'],
                'body' => $post['body'],
                'image_path' => 'embedded',
                'image_mime' => 'image/svg+xml',
                'image_base64' => $this->sampleImageBase64(
                    $post['title'],
                    $categories->firstWhere('slug', $post['category_slug'])->name,
                    $post['accent'],
                    $post['secondary']
                ),
                'is_public' => true,
                'published_at' => now()->subDays($post['published_days_ago']),
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function samplePosts(): array
    {
        return [
            [
                'category_slug' => 'technology',
                'slug' => 'how-does-a-database-index-speed-up-queries',
                'title' => 'How does a database index speed up queries?',
                'summary' => 'Indexes organize data so the database can find rows faster without scanning every record.',
                'body' => 'A database index works like the index in a book. Instead of reading every page, the database can jump to the section it needs. Under the hood, indexes usually store keys in a tree or similar structure, which makes lookups faster and more predictable. The tradeoff is extra storage and slower writes, because the index must be updated whenever data changes.',
                'published_days_ago' => 6,
                'accent' => '#0ea5e9',
                'secondary' => '#1d4ed8',
            ],
            [
                'category_slug' => 'biology',
                'slug' => 'why-does-sleep-help-you-remember-things',
                'title' => 'Why does sleep help you remember things?',
                'summary' => 'Sleep gives the brain time to strengthen useful memories and sort out less important information.',
                'body' => 'During sleep, the brain replays and reorganizes information from the day. That process helps strengthen connections related to important memories while trimming away less useful noise. Different sleep stages support different kinds of memory, which is one reason studying a little and sleeping well is usually better than cramming all night.',
                'published_days_ago' => 5,
                'accent' => '#16a34a',
                'secondary' => '#0f766e',
            ],
            [
                'category_slug' => 'chemistry',
                'slug' => 'why-does-salt-help-melt-ice',
                'title' => 'Why does salt help melt ice?',
                'summary' => 'Salt lowers the freezing point of water, making it harder for ice to stay solid.',
                'body' => 'Pure water freezes at a specific temperature, but dissolved salt changes that balance. When salt mixes with thin water on top of ice, it lowers the freezing point and encourages the ice to melt. This is why roads can be treated in winter, although very low temperatures can limit how effective salt is.',
                'published_days_ago' => 4,
                'accent' => '#f97316',
                'secondary' => '#b45309',
            ],
            [
                'category_slug' => 'physics',
                'slug' => 'what-causes-a-rainbow-in-the-sky',
                'title' => 'What causes a rainbow in the sky?',
                'summary' => 'Rainbows appear when light bends, reflects, and splits into colors inside water droplets.',
                'body' => 'A rainbow forms when sunlight enters a raindrop, bends as it slows down, reflects off the back of the droplet, and bends again as it exits. Different wavelengths bend by different amounts, so the white light separates into a visible band of colors. The result depends on your viewing angle, which is why rainbows appear in a specific direction relative to the sun.',
                'published_days_ago' => 3,
                'accent' => '#8b5cf6',
                'secondary' => '#4f46e5',
            ],
            [
                'category_slug' => 'technology',
                'slug' => 'what-is-an-api-in-simple-terms',
                'title' => 'What is an API in simple terms?',
                'summary' => 'An API is a contract that lets software systems ask each other for data or actions.',
                'body' => 'An API is a structured way for one program to request something from another. It defines the available endpoints, what inputs they accept, and what outputs they return. In practice, APIs let apps share data cleanly without needing to know how the other system works internally. That is why they are essential for modern web apps, mobile apps, and integrations.',
                'published_days_ago' => 2,
                'accent' => '#06b6d4',
                'secondary' => '#0ea5e9',
            ],
            [
                'category_slug' => 'biology',
                'slug' => 'how-does-the-heart-pump-blood-through-the-body',
                'title' => 'How does the heart pump blood through the body?',
                'summary' => 'The heart uses coordinated muscle contractions and valves to push blood in one direction.',
                'body' => 'The heart is a muscle with four chambers. When it contracts, it pushes blood through valves that keep the flow moving forward. The right side sends blood to the lungs for oxygen, and the left side sends oxygen-rich blood to the rest of the body. That repeating cycle is what keeps tissues supplied with oxygen and nutrients.',
                'published_days_ago' => 1,
                'accent' => '#ef4444',
                'secondary' => '#be123c',
            ],
            [
                'category_slug' => 'chemistry',
                'slug' => 'why-does-iron-rust-over-time',
                'title' => 'Why does iron rust over time?',
                'summary' => 'Rust forms when iron reacts with oxygen and moisture in the environment.',
                'body' => 'Rust is a chemical reaction that happens when iron is exposed to oxygen and water. The iron slowly oxidizes and forms a flaky reddish-brown surface. Humidity, salt, and temperature changes can speed up the process. Paint, coatings, and regular maintenance help slow rust by keeping moisture and oxygen away from the metal.',
                'published_days_ago' => 0,
                'accent' => '#f59e0b',
                'secondary' => '#7c2d12',
            ],
        ];
    }

    private function sampleImageBase64(string $title, string $category, string $accent, string $secondary): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $category = htmlspecialchars($category, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$accent}"/>
            <stop offset="100%" stop-color="{$secondary}"/>
        </linearGradient>
        <radialGradient id="glow" cx="30%" cy="25%" r="75%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.35"/>
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
        </radialGradient>
    </defs>
    <rect width="1200" height="630" fill="url(#bg)"/>
    <rect width="1200" height="630" fill="url(#glow)"/>
    <circle cx="970" cy="110" r="95" fill="#ffffff" opacity="0.12"/>
    <circle cx="1030" cy="160" r="38" fill="#ffffff" opacity="0.10"/>
    <circle cx="160" cy="520" r="120" fill="#ffffff" opacity="0.08"/>
    <text x="80" y="120" fill="#eff6ff" font-size="42" font-family="Arial, sans-serif" font-weight="700" letter-spacing="2">{$category}</text>
    <text x="80" y="265" fill="#ffffff" font-size="72" font-family="Arial, sans-serif" font-weight="700">{$title}</text>
    <text x="80" y="340" fill="#e0f2fe" font-size="30" font-family="Arial, sans-serif">BrainBites sample post</text>
    <rect x="80" y="420" width="260" height="14" rx="7" fill="#ffffff" opacity="0.8"/>
    <rect x="80" y="450" width="180" height="14" rx="7" fill="#ffffff" opacity="0.6"/>
    <rect x="80" y="480" width="220" height="14" rx="7" fill="#ffffff" opacity="0.45"/>
</svg>
SVG;

        return base64_encode($svg);
    }
}
