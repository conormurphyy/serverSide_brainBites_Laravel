<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function brainbot(): View
    {
        return view('pages.brainbot');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'topic' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:20', 'max:4000'],
        ]);

        ContactMessage::create($data);

        return redirect()
            ->route('contact')
            ->with('status', 'Thanks! Your message has been sent to the BrainBites team.');
    }
}
