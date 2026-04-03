@extends('layouts.site')

@section('title', 'brainBot | BrainBites AI Lab')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-kicker">AI Lab</p>
            <h1 class="bb-title-font text-4xl text-white sm:text-5xl">brainBot Command Deck</h1>
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

    <section class="bb-brainbot bb-brainbot-standalone" id="brainbotPanel" aria-live="polite">
        <header class="bb-brainbot-header">
            <h2>brainBot</h2>
            <p>Dedicated learning chat</p>
        </header>

        <div class="bb-brainbot-messages" id="brainbotMessages">
            <article class="bb-brainbot-message bot">
Welcome to brainBot. Ask a question to begin your session.
            </article>
        </div>

        <form class="bb-brainbot-form" id="brainbotForm">
            <label for="brainbotInput" class="sr-only">Ask brainBot</label>
            <input id="brainbotInput" type="text" maxlength="500" placeholder="Ask brainBot anything..." required>
            <button type="submit">Send</button>
        </form>
    </section>
@endsection
