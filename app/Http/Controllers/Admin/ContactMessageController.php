<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $section = (string) $request->string('section', 'contacts');
        if (! in_array($section, ['contacts', 'posts', 'users'], true)) {
            $section = 'contacts';
        }

        $filter = (string) $request->string('filter', 'open');
        if (! in_array($filter, ['open', 'resolved', 'all', 'pending', 'approved', 'rejected', 'active', 'banned'], true)) {
            $filter = 'open';
        }

        $messages = ContactMessage::query()
            ->with('resolver')
            ->when($filter === 'open', fn ($query) => $query->where('is_resolved', false))
            ->when($filter === 'resolved', fn ($query) => $query->where('is_resolved', true))
            ->latest()
            ->paginate(20, ['*'], 'contacts_page')
            ->withQueryString();

        $postFilter = in_array($filter, ['pending', 'approved', 'rejected', 'all'], true) ? $filter : 'pending';
        $posts = Post::query()
            ->with(['user', 'category'])
            ->when($postFilter !== 'all', fn ($query) => $query->where('approval_status', $postFilter))
            ->latest('created_at')
            ->paginate(12, ['*'], 'posts_page')
            ->withQueryString();

        $userFilter = in_array($filter, ['active', 'banned', 'all'], true) ? $filter : 'active';
        $users = User::query()
            ->where('role', '!=', 'admin')
            ->when($userFilter === 'active', fn ($query) => $query->where('is_banned', false))
            ->when($userFilter === 'banned', fn ($query) => $query->where('is_banned', true))
            ->latest('created_at')
            ->paginate(15, ['*'], 'users_page')
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'posts' => $posts,
            'users' => $users,
            'section' => $section,
            'postFilter' => $postFilter,
            'userFilter' => $userFilter,
            'filter' => $filter,
        ]);
    }

    public function toggleResolved(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        if ($contactMessage->is_resolved) {
            $contactMessage->update([
                'is_resolved' => false,
                'resolved_by' => null,
                'resolved_at' => null,
            ]);

            return back()->with('status', 'Message reopened.');
        }

        $contactMessage->update([
            'is_resolved' => true,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('status', 'Message marked as resolved.');
    }

    public function approvePost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $post->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejected_at' => null,
            'review_notes' => null,
        ]);

        return back()->with('status', 'Post approved and now eligible for public visibility.');
    }

    public function rejectPost(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $post->update([
            'approval_status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_at' => now(),
            'review_notes' => filled($data['review_notes'] ?? null) ? trim((string) $data['review_notes']) : null,
        ]);

        return back()->with('status', 'Post rejected and removed from public visibility.');
    }

    public function toggleBanUser(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_if($user->isAdmin(), 422);

        if ($request->user()->is($user)) {
            return back()->with('status', 'You cannot ban your own admin account.');
        }

        if ($user->is_banned) {
            $user->update([
                'is_banned' => false,
                'banned_at' => null,
                'ban_reason' => null,
            ]);

            return back()->with('status', 'User ban removed.');
        }

        $data = $request->validate([
            'ban_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => filled($data['ban_reason'] ?? null) ? trim((string) $data['ban_reason']) : 'Violation of community rules.',
        ]);

        return back()->with('status', 'User has been banned.');
    }
}
