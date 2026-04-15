# BrainBites

BrainBites is a Laravel-based, visual learning and discussion platform focused on question-driven study. It combines long-form posts, community interaction, profile features, and an integrated assistant called brainBot.

This README is written as an operational project guide for local development and maintenance.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Blade](https://img.shields.io/badge/Blade-FF6A00?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)

## Table of Contents

1. [Project Overview](#project-overview)
2. [Screenshot Placeholders](#screenshot-placeholders)
3. [Core Features](#core-features)
4. [Technology Stack](#technology-stack)
5. [Requirements](#requirements)
6. [Local Setup](#local-setup)
7. [Environment Configuration](#environment-configuration)
8. [Running the Project](#running-the-project)
9. [Route Reference](#route-reference)
10. [Data and Schema Notes](#data-and-schema-notes)
11. [brainBot Integration Notes](#brainbot-integration-notes)
12. [Admin Operations](#admin-operations)
13. [Testing and Code Quality](#testing-and-code-quality)
14. [Troubleshooting](#troubleshooting)
15. [Deployment Checklist](#deployment-checklist)
16. [License](#license)

## Project Overview

BrainBites supports the full flow of community-based learning:

- Publish and explore educational posts with categories and metadata.
- Interact through likes, bookmarks, follows, and threaded comments.
- Navigate public creator profiles and a following feed.
- Use brainBot for guided Q&A with model-generated responses and source context.
- Provide support feedback through a contact form and admin inbox workflow.

The application is designed as a classic Laravel web app with server-rendered views, progressive JavaScript enhancements, and Vite-powered asset compilation.

## Screenshot Placeholders

Add screenshots in the sections below as the UI evolves. Keep image files under a folder such as `docs/images/`.

### Home / Explore Feed

![Home Feed Screenshot](docs/images/home-feed.png)

### Post Detail and Comments

![Post Detail Screenshot](docs/images/post-detail.png)

### brainBot Interface

![brainBot Screenshot](docs/images/brainbot.png)

### Public Profile

![Public Profile Screenshot](docs/images/public-profile.png)

### Admin Contact Inbox

![Admin Contact Inbox Screenshot](docs/images/admin-contact-inbox.png)

## Core Features

### Content and Discovery

- Post creation, update, and deletion with authorization checks.
- Post listing with filtering/sorting support.
- Category-based exploration.
- Dedicated glossary and informational pages.

### Social and Engagement

- Like and unlike posts.
- Bookmark and unbookmark posts.
- Follow and unfollow users.
- Following feed for relationship-based discovery.

### Comments and Discussion

- Nested comments/replies.
- Comment upvotes.
- Comment actions wired for responsive UX.

### User Profiles

- Public profile route by username.
- Authenticated account profile management.
- Creator-centric interaction points in post and profile views.

### brainBot Assistant

- Dedicated chat page.
- API endpoint for assistant responses.
- Optional response history for authenticated users.
- Configurable model and fallback chain through environment settings.

### Contact and Moderation

- Public contact form.
- Admin-only inbox view.
- Resolve/reopen workflow for contact messages.

## Technology Stack

- PHP 8.3+
- Laravel 13
- MySQL or SQLite (environment-dependent)
- Blade templates
- Vite 8
- Tailwind CSS
- Alpine.js
- Axios
- Laravel Socialite (Google auth/linking)

## Requirements

Minimum local requirements:

- PHP 8.3 or newer
- Composer
- Node.js 20+ and npm
- MySQL 8+ (if not using SQLite)

Recommended:

- Git
- A local mail catcher for email flow testing

## Local Setup

### 1. Clone and enter the project

```bash
git clone <your-repository-url>
cd serverSide_brainBites_Laravel
```

### 2. Install backend and frontend dependencies

```bash
composer install
npm install
```

### 3. Initialize environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database

For SQLite (default-friendly local setup):

```dotenv
DB_CONNECTION=sqlite
```

For MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brainbites
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run migrations

```bash
php artisan migrate
```

If you are importing from SQL dumps, inspect migration state first and only run missing migrations.

### 6. Build assets

```bash
npm run build
```

### 7. Start the application

```bash
php artisan serve
```

Open your app at the URL printed by Artisan (commonly `http://127.0.0.1:8000`).

## Environment Configuration

Configure these values in `.env` based on your local environment.

### Application

```dotenv
APP_NAME=BrainBites
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### Session, Cache, Queue

Project defaults are database-backed session/cache/queue drivers. Ensure required tables exist via migrations.

```dotenv
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### brainBot (OpenRouter)

```dotenv
BRAINBOT_MODEL=meta-llama-3-8b-instruct
BRAINBOT_FALLBACK_MODELS=openai/gpt-oss-20b:free,meta-llama/llama-3.2-3b-instruct:free,google/gemma-3n-e4b-it:free,liquid/lfm-2.5-1.2b-instruct:free
BRAINBOT_OPENROUTER_URL=https://openrouter.ai/api/v1/chat/completions
BRAINBOT_OPENROUTER_KEY=your-openrouter-api-key-here
```

### Google OAuth (optional)

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

## Running the Project

### Standard development workflow

Use separate terminals:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

### Combined workflow through Composer

```bash
composer run dev
```

This command starts the web server, queue listener, log stream, and Vite in parallel.

### Production build of frontend assets

```bash
npm run build
```

## Route Reference

Key routes currently available:

### Public

- `GET /` home feed
- `GET /posts` explore posts
- `GET /posts/{post}` post detail
- `GET /about` about page
- `GET /glossary` glossary page
- `GET /contact` contact form
- `POST /contact` submit contact form
- `GET /brainbot` brainBot page
- `POST /brainbot/chat` assistant chat endpoint (throttled)
- `GET /brainbot/history` assistant history endpoint (throttled)
- `GET /u/{user:username}` public user profile

### Authenticated

- `GET /dashboard`
- `GET /posts/create`
- `POST /posts`
- `GET /posts/{post}/edit`
- `PUT /posts/{post}`
- `DELETE /posts/{post}`
- `POST /posts/{post}/like`
- `POST /posts/{post}/bookmark`
- `POST /posts/{post}/comments`
- `DELETE /posts/{post}/comments/{comment}`
- `POST /posts/{post}/comments/{comment}/upvote`
- `POST /users/{user}/follow`
- `GET /following`
- `GET /bookmarks`
- `GET /profile`
- `PATCH /profile`
- `DELETE /profile`

### Admin

- `GET /admin/contact-messages`
- `PATCH /admin/contact-messages/{contactMessage}/resolve`

## Data and Schema Notes

The schema supports content, social features, and moderation workflows:

- `users` includes role and profile-related fields.
- `posts` stores core content and publication metadata.
- `categories` classifies posts.
- `comments` supports thread structure.
- `comment_votes` tracks comment upvotes.
- `likes` tracks post likes.
- `bookmarks` stores saved posts per user.
- `brainbot_messages` persists assistant history for signed-in users.
- `contact_messages` stores public contact submissions.

The repository also includes SQL resources in `database/` for large seed/schema operations.

## brainBot Integration Notes

brainBot behavior is controlled by `App\Services\BrainBotService`.

Key implementation details:

- Requests are sent to OpenRouter chat completions endpoint.
- The service can iterate through fallback models on throttling/deprecation failures.
- Simple web context gathering is included via public search sources.
- Authenticated requests store question/answer/source payloads in `brainbot_messages`.

Operational notes:

- Missing/invalid OpenRouter configuration results in fallback handling.
- API failures and malformed responses are logged in `storage/logs/laravel.log`.
- If free-only usage is required, keep model IDs suffixed with `:free`.

## Admin Operations

To grant admin privileges, update a user record role value to `admin`.

Example:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'admin@example.com';
```

Admin capabilities include:

- Accessing contact inbox entries.
- Resolving and reopening contact tickets.
- Elevated control over content management (based on policy checks and role conditions).

## Testing and Code Quality

Run test suite:

```bash
composer test
```

Run PHPUnit directly:

```bash
php artisan test
```

Format code with Laravel Pint:

```bash
./vendor/bin/pint
```

## Troubleshooting

### Route returns 404 unexpectedly

- Run `php artisan route:list` and verify route registration.
- Confirm cached routes/config are not stale.

### Queue-backed features are not processing

- Ensure queue worker is running.
- Confirm database queue tables exist.

### brainBot is not returning model answers

- Validate OpenRouter environment variables.
- Check `storage/logs/laravel.log` for API status/error payloads.
- Confirm model ID is a chat-capable model, not embedding-only.

### OAuth login issues

- Verify callback URL exactly matches provider settings.
- Re-check `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and redirect URI.

## Deployment Checklist

Before deploying to production:

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Configure production database credentials.
3. Set all required API keys/secrets.
4. Run `composer install --no-dev --optimize-autoloader`.
5. Run `php artisan migrate --force`.
6. Build assets with `npm ci && npm run build`.
7. Cache configuration/routes/views as needed.
8. Start queue worker and monitor logs.

## License

This project is licensed under the MIT License.


# BrainBites Website Status (So Far)

## 1. Project Snapshot
BrainBites is a visual-first Q&A platform built with Laravel, focused on curiosity-driven learning.

The app currently supports:
1. Community posts with rich visuals and categories
2. Social actions (likes, bookmarks, follows)
3. Nested comments with improved UX
4. AI assistant workflows (brainBot)
5. Public user profiles
6. PWA basics (manifest + service worker + offline page)

## 2. Current Tech Stack
1. Laravel 12+
2. PHP 8.2+
3. MySQL
4. Vite asset pipeline
5. Alpine.js + custom vanilla JavaScript
6. OpenRouter API integration for brainBot

## 3. Core User Features
### 3.1 Posts
1. Create, edit, delete posts (policy-based authorization)
2. Public and draft visibility support
3. Scheduled publishing support via published_at
4. Category filtering, searching, and sorting
5. Reading-time and difficulty indicators
6. Related-post recommendations on post detail pages

### 3.2 Engagement
1. Like/unlike posts (non-admin users)
2. Bookmark/unsave posts (non-admin users)
3. Follow/unfollow creators
4. Dedicated Following feed page

### 3.3 Comments (Upgraded)
1. Nested comments and replies
2. Upvote helpful comments
3. Sort comments by Top and New
4. Collapsible long reply threads with show more/show fewer
5. AJAX comment posting and upvoting (no full-page refresh)

### 3.4 Public Profiles
1. Public profile route: /u/{username}
2. Profile includes:
   1. Avatar, display name, username, bio
   2. Follower and following counts
   3. Public post and likes stats
   4. Top posts (most liked)
   5. Recent posts
3. Follow/unfollow available from public profile when authenticated
4. Author names across post views link to public profiles

### 3.5 brainBot
1. Dedicated chat page
2. Post-level contextual question prompts
3. Inline paragraph simplification tools
4. Revision and flashcard-related helper workflows

## 4. UI/UX Improvements Already Added
1. Themed global footer aligned with site style
2. Improved navbar ordering and active-page highlighting
3. Reusable back-navigation button across pages
4. Comments moved below post content (full-width discussion flow)
5. Stronger action button styling for inline paragraph tools
6. Interactive table of contents:
   1. Active section tracking
   2. Smooth scroll navigation
   3. Progress indicator

## 5. PWA Support (Baseline)
1. Web app manifest added
2. Service worker registration added
3. Offline fallback page added
4. Localhost service worker behavior adjusted for dev cache stability

## 6. Access and Navigation
### 6.1 Main Routes
1. / -> Home/Explore feed
2. /posts -> Explore posts
3. /posts/{post} -> Post detail
4. /brainbot -> brainBot page
5. /glossary -> Glossary page
6. /following -> Following feed (auth)
7. /bookmarks -> Bookmarks (auth)
8. /dashboard -> Dashboard (auth)
9. /profile -> Profile settings (auth, non-admin)
10. /u/{username} -> Public creator profile

### 6.2 Admin-Oriented
1. /admin/contact-messages -> Admin inbox

## 7. Data Model Highlights
1. users table includes role, google_id, profile_photo_path, username, bio
2. posts includes content, image fields, visibility/publish state, slug
3. follows for follower-followed user relationships
4. comments supports nested threads via parent_comment_id
5. comment_votes supports helpful upvotes on comments
6. likes and bookmarks support post interactions

## 8. Operational Status
1. Database migrations include new social/profile/comment-vote schema
2. Frontend builds successfully with Vite
3. Core UX flows implemented and connected in UI

## 9. Recommended Next Steps
1. Notifications center (replies, upvotes, followed-user activity)
2. Creator badges/expertise tags for profile discovery
3. Saved bookmark collections (folders)
4. Real-time updates for comments and votes via broadcasting

## 10. Quick Summary
BrainBites has progressed from a post feed into a social, profile-driven learning platform with improved commenting UX, creator discovery, and foundational PWA support.
