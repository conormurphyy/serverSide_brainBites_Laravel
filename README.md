<img src="Untitleddesign-ezgif.com-video-to-gif-converter.gif" />

# BrainBites

Visual-first Q&A platform built with Laravel, designed for curiosity-driven learning.

BrainBites combines community posts, rich visuals, category exploration, and an AI assistant called brainBot in one polished web app.

## Highlights

- Visual question capsules with image-backed posts
- Interactive homepage with topic exploration and community stats
- Dedicated brainBot page with conversation history
- OpenRouter-powered AI responses with web-context support
- About and Contact pages with custom visuals
- Admin contact inbox with resolve/reopen workflow
- Light/Dark mode toggle with persisted preference
- Role-aware permissions:
	- Admins can edit/delete any post
	- Users can edit/delete only their own posts

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL
- Vite (JS/CSS build pipeline)
- Alpine.js + custom vanilla JS interactions
- OpenRouter API (for brainBot)

## Core Routes

- `/` Home feed
- `/posts` Explore posts
- `/posts/create` Create post (auth)
- `/posts/{post}` View post
- `/posts/{post}/edit` Edit post (authorized)
- `/brainbot` Dedicated chatbot page
- `/brainbot/chat` Chat endpoint
- `/brainbot/history` Auth user chat history
- `/about` About page
- `/contact` Contact form
- `/admin/contact-messages` Admin inbox (admin only)

## Local Setup

1. Install dependencies

```bash
composer install
npm install
```

2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

3. Update `.env` database values

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brainbites
DB_USERNAME=root
DB_PASSWORD=
```

4. Configure brainBot (OpenRouter)

```dotenv
BRAINBOT_MODEL=openai/gpt-oss-120b:free
BRAINBOT_OPENROUTER_URL=https://openrouter.ai/api/v1/chat/completions
BRAINBOT_OPENROUTER_KEY=your-openrouter-key
```

5. Run migrations

```bash
php artisan migrate
```

If your database was imported manually and base tables already exist, run only feature migrations as needed:

```bash
php artisan migrate --path=database/migrations/2026_04_03_000100_create_contact_messages_table.php
php artisan migrate --path=database/migrations/2026_04_03_000200_create_brainbot_messages_table.php
```

6. Build assets and serve

```bash
npm run build
php artisan serve
```

## Admin Access

Set a user as admin by updating `users.role` to `admin`.

Example SQL:

```sql
UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
```

Admins can:

- Edit/delete all posts
- Access contact inbox at `/admin/contact-messages`
- Resolve/reopen contact messages

## brainBot Notes

- brainBot stores authenticated user history in `brainbot_messages`
- API failures are logged in `storage/logs/laravel.log`
- If OpenRouter rejects requests due to provider restrictions, update OpenRouter account policy/provider settings

## UX Features

- Inline friendly validation messages for post forms
- Modal-based delete confirmation (no browser `confirm()`)
- Dark mode with local storage persistence
- Animated visual elements on home/about/contact

## Troubleshooting

### `/posts/create` or `/posts/{post}/edit` returns 404

Run:

```bash
php artisan route:list
```

Confirm static routes (`posts/create`) are registered before dynamic post routes.

### App seems stuck on delete prompt overlay

Rebuild and hard refresh:

```bash
npm run build
```

Then refresh browser with `Ctrl+F5`.

### OpenRouter endpoint looks "not found" in browser

That is expected. It is an API endpoint and must be called via POST with headers + JSON payload.

## Development Commands

```bash
# Laravel routes
php artisan route:list

# Build frontend assets
npm run build

# Watch assets (dev)
npm run dev
```

## License

This project is open-source and available under the MIT license.

## Final Report

BrainBites is now a polished Laravel-based, visual-first Q&A platform focused on curiosity-driven learning. The site includes community posts, categories, likes, bookmarks, follows, nested comments, public profiles, and a dedicated brainBot experience built on OpenRouter.

### Completed Work

- Core post feed, post detail, create, edit, and delete flows
- Social interactions including likes, bookmarks, and follows
- Nested comments with voting and AJAX interactions
- Public creator profiles and profile-linked author navigation
- brainBot chat experience with conversation history
- Admin contact inbox with resolve and reopen actions
- PWA baseline support with manifest, service worker, and offline fallback
- Dark mode and improved UI polish across key pages

### Current Status

- Main application flows are implemented and connected in the UI
- Database schema, migrations, and frontend asset builds are in place
- The project is in a final, usable state and ready for continued iteration or deployment hardening

### Suggested Next Steps

- Add notifications for replies, upvotes, and follow activity
- Improve search and discovery for posts and profiles
- Expand real-time updates for comments and engagement
- Continue performance and security hardening before production release


