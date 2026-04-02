<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $filter = (string) $request->string('filter', 'open');

        $messages = ContactMessage::query()
            ->with('resolver')
            ->when($filter === 'open', fn ($query) => $query->where('is_resolved', false))
            ->when($filter === 'resolved', fn ($query) => $query->where('is_resolved', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
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
}
