@extends('layouts.site')

@section('title', 'brainBot | BrainBites AI Lab')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-kicker">AI Lab</p>
            <h1 class="bb-title-font text-4xl text-white sm:text-5xl">Brain Bot Command Deck</h1>
            <p class="mt-4 max-w-xl text-sm text-cyan-100/90 sm:text-base">
                Ask questions, summarize tricky concepts, and explore ideas with a web-aware assistant.
                This is your dedicated chat space for focused learning sessions.
            </p>

            <div class="mt-6 flex flex-wrap gap-2">
                <button type="button" class="bb-topic-pill" data-brainbot-prompt="Explain photosynthesis like I am 12.">For Kids</button>
                <button type="button" class="bb-topic-pill" data-brainbot-prompt="Summarize the causes of World War 1 in 6 bullets.">Quick Summary</button>
                <button type="button" class="bb-topic-pill" data-brainbot-prompt="Help me study Newton's Laws with examples.">Study Coach</button>
            </div>
        </div>

        <div class="grid gap-3">
            <article class="bb-focus-card text-cyan-50">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Mode</p>
                <h3 class="mt-1 text-lg font-semibold">Web + Model Synthesis</h3>
                <p class="mt-2 text-sm text-cyan-100/90">Answers blend model knowledge with live context and source snippets.</p>
            </article>
            <article class="bb-focus-card text-cyan-50">
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Best Prompting Tip</p>
                <h3 class="mt-1 text-lg font-semibold">Ask Narrow, Then Expand</h3>
                <p class="mt-2 text-sm text-cyan-100/90">Start with one precise question, then ask follow-ups for depth.</p>
            </article>
        </div>
    </section>

    <section class="bb-brainbot bb-brainbot-standalone" id="brainbotPanel" aria-live="polite" style="font-size: 1rem;">
        <header class="bb-brainbot-header" style="padding: 1rem 1.1rem;">
            <h2 style="font-size: 1.3rem; line-height: 1.2;">Brain Bot</h2>
            <p style="font-size: 0.95rem;">Dedicated learning chat</p>
        </header>

        <div class="bb-brainbot-messages" id="brainbotMessages" style="padding: 1rem; gap: 0.85rem;">
            <article class="bb-brainbot-message bot" style="font-size: 1.02rem; line-height: 1.65; white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word;">
Welcome to Brain Bot. Ask a question to begin your session.
            </article>
        </div>

        <form class="bb-brainbot-form" id="brainbotForm" style="padding: 0.9rem;">
            <label for="brainbotInput" class="sr-only">Ask Brain Bot</label>
            <input id="brainbotInput" type="text" maxlength="500" placeholder="Ask Brain Bot anything..." required style="font-size: 1rem; padding: 0.72rem 0.8rem;">
            <button type="submit" style="font-size: 0.95rem; padding: 0.62rem 1rem;">Send</button>
        </form>
    </section>
@endsection
