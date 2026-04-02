@extends('layouts.site')

@section('title', 'Admin | Contact Inbox')

@section('content')
    <section class="bb-cosmic-banner mb-8">
        <div>
            <p class="bb-kicker">Admin</p>
            <h1 class="bb-title-font text-4xl text-white sm:text-5xl">Contact Inbox</h1>
            <p class="mt-4 max-w-xl text-sm text-cyan-100/90 sm:text-base">
                Review incoming messages, mark resolved threads, and track who handled each request.
            </p>
        </div>

        <div class="bb-focus-card text-cyan-50">
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-200">Queue State</p>
            <p class="mt-2 text-lg font-semibold">{{ $messages->total() }} total messages</p>
            <p class="mt-2 text-sm text-cyan-100/90">Current filter: {{ ucfirst($filter) }}</p>
        </div>
    </section>

    <section class="bb-glass p-5 sm:p-6">
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.contact-messages.index', ['filter' => 'open']) }}" class="bb-button-secondary {{ $filter === 'open' ? 'border-cyan-500 text-cyan-700' : '' }}">Open</a>
            <a href="{{ route('admin.contact-messages.index', ['filter' => 'resolved']) }}" class="bb-button-secondary {{ $filter === 'resolved' ? 'border-cyan-500 text-cyan-700' : '' }}">Resolved</a>
            <a href="{{ route('admin.contact-messages.index', ['filter' => 'all']) }}" class="bb-button-secondary {{ $filter === 'all' ? 'border-cyan-500 text-cyan-700' : '' }}">All</a>
        </div>

        @if ($messages->isEmpty())
            <p class="text-sm text-slate-600">No messages for this filter.</p>
        @else
            <div class="space-y-4">
                @foreach ($messages as $message)
                    <article class="bb-card">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-cyan-700">{{ $message->topic }}</p>
                                <h2 class="text-lg font-bold text-slate-900">{{ $message->name }} <span class="text-sm font-medium text-slate-500">({{ $message->email }})</span></h2>
                            </div>
                            <span class="bb-chip">{{ $message->is_resolved ? 'Resolved' : 'Open' }}</span>
                        </div>

                        <p class="mt-3 whitespace-pre-wrap text-sm text-slate-700">{{ $message->message }}</p>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                            <span>Received: {{ $message->created_at?->format('M d, Y H:i') }}</span>
                            @if ($message->is_resolved)
                                <span>
                                    Resolved by {{ $message->resolver?->name ?? 'Unknown' }} on {{ $message->resolved_at?->format('M d, Y H:i') }}
                                </span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('admin.contact-messages.resolve', $message) }}" class="mt-4">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bb-button-secondary">
                                {{ $message->is_resolved ? 'Mark as Open' : 'Mark as Resolved' }}
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $messages->links() }}
            </div>
        @endif
    </section>
@endsection
