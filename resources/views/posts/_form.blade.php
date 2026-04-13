@php
    $editing = isset($post);
@endphp

<div class="grid gap-6">
    <div>
        <label for="title" class="bb-label">Question Title</label>
        <input
            type="text"
            id="title"
            name="title"
            class="bb-input"
            value="{{ old('title', $post->title ?? '') }}"
            minlength="8"
            maxlength="120"
            required
            placeholder="How was the first programming language created?"
        >
        @error('title')<p class="bb-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="category_id" class="bb-label">Category</label>
        <select id="category_id" name="category_id" class="bb-select" required>
            <option value="">Choose a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id ?? '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<p class="bb-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="summary" class="bb-label">Short Summary</label>
        <textarea
            id="summary"
            name="summary"
            class="bb-textarea"
            rows="3"
            minlength="20"
            maxlength="280"
            required
            data-counter-target="summaryCounter"
            placeholder="Give a concise and digestible overview of your answer."
        >{{ old('summary', $post->summary ?? '') }}</textarea>
        <p class="mt-1 text-xs text-slate-500"><span id="summaryCounter">0</span>/280 characters</p>
        @error('summary')<p class="bb-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="body" class="bb-label">Detailed Explanation</label>
        <textarea
            id="body"
            name="body"
            class="bb-textarea"
            rows="10"
            minlength="80"
            required
            placeholder="Write the full explanation using plain language and accurate information."
        >{{ old('body', $post->body ?? '') }}</textarea>
        @error('body')<p class="bb-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="image" class="bb-label">Feature Image</label>
        <input
            type="file"
            id="image"
            name="image"
            class="bb-file-input"
            accept=".jpg,.jpeg,.png,.webp"
            @if (! $editing) required @endif
        >
        <p id="imageName" class="mt-1 text-xs text-slate-500">No file selected</p>
        @if ($editing)
            <p class="mt-1 text-xs text-slate-500">Leave this empty to keep the current image.</p>
        @endif
        @error('image')<p class="bb-error">{{ $message }}</p>@enderror
    </div>

    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
        <input
            type="checkbox"
            name="is_public"
            value="1"
            class="rounded border-slate-300 text-cyan-700 focus:ring-cyan-500"
            @checked(old('is_public', $post->is_public ?? true))
        >
        Make this post public
    </label>

    <div>
        <label for="published_at" class="bb-label">Publish At (optional)</label>
        <input
            type="datetime-local"
            id="published_at"
            name="published_at"
            class="bb-input"
            value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\\TH:i') : '') }}"
        >
        <p class="mt-1 text-xs text-slate-500">Leave empty to publish immediately when public is enabled.</p>
        @error('published_at')<p class="bb-error">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="bb-button">{{ $editing ? 'Save Changes' : 'Publish Post' }}</button>
        <a href="{{ $editing ? route('posts.show', $post) : route('dashboard') }}" class="bb-button-secondary">Cancel</a>
    </div>
</div>
