<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="title" class="label">Title</label>
        <input id="title" name="title" value="{{ old('title', $link->title) }}" maxlength="100" required class="input" placeholder="Instagram">
        <x-field-error name="title" />
    </div>
    <div>
        <label for="icon" class="label">Icon</label>
        <select id="icon" name="icon" class="input">
            @foreach (config('kustore.link_icons') as $value => $label)
                <option value="{{ $value }}" @selected(old('icon', $link->icon ?: 'custom') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-2">
        <label for="url" class="label">URL</label>
        <input id="url" name="url" type="url" value="{{ old('url', $link->url) }}" required class="input" placeholder="https://instagram.com/yourname">
        <x-field-error name="url" />
    </div>
</div>
<label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="is_visible" value="1" class="checkbox" @checked(old('is_visible', $link->exists ? $link->is_visible : true))> Show on my page</label>
