@extends('layouts.dashboard')
@section('page_title', 'Store')
@section('page_subtitle', 'Your profile, look and payment details.')
@section('page_actions')
    <a href="{{ $store->url() }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">{{ icon('external-link', 'size-4') }} Preview</a>
@endsection

@section('content')
<form method="POST" action="{{ route('dashboard.store.update') }}" enctype="multipart/form-data" class="space-y-4 sm:space-y-6" novalidate>
    @csrf @method('PUT')

    <section class="card card-pad">
        <h2 class="text-base font-semibold">Profile</h2>
        <div class="mt-5 flex items-center gap-4">
            @if ($store->avatarUrl())
                <img id="avatar-preview" src="{{ $store->avatarUrl() }}" alt="" class="size-20 rounded-full object-cover ring-1 ring-line dark:ring-white/10">
            @else
                <img id="avatar-preview" src="data:," alt="" class="size-20 rounded-full object-cover" hidden>
                <span id="avatar-preview-placeholder" class="grid size-20 place-items-center rounded-full bg-navy font-display text-xl font-semibold text-white dark:bg-white dark:text-navy">{{ $store->initials() }}</span>
            @endif
            <div class="space-y-2">
                <label class="btn btn-secondary btn-sm">{{ icon('upload', 'size-4') }} Upload photo or logo
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" data-preview="avatar-preview">
                </label>
                <p class="help mt-0">JPG, PNG or WebP, up to 4 MB.</p>
                @if ($store->avatar_path)
                    <label class="flex items-center gap-2 text-sm muted"><input type="checkbox" name="remove_avatar" value="1" class="checkbox"> Remove current photo</label>
                @endif
            </div>
        </div>
        <x-field-error name="avatar" />

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="display_name" class="label">Display name</label>
                <input id="display_name" name="display_name" value="{{ old('display_name', $store->display_name) }}" maxlength="80" required class="input">
                <x-field-error name="display_name" />
            </div>
            <div class="sm:col-span-2">
                <label for="bio" class="label">Bio</label>
                <textarea id="bio" name="bio" maxlength="500" rows="3" class="input" placeholder="What you do, in a sentence or two.">{{ old('bio', $store->bio) }}</textarea>
                <x-field-error name="bio" />
            </div>
            <div>
                <label for="location" class="label">Location</label>
                <input id="location" name="location" value="{{ old('location', $store->location) }}" maxlength="100" class="input" placeholder="Jakarta">
                <x-field-error name="location" />
            </div>
            <div>
                <label for="website" class="label">Website</label>
                <input id="website" name="website" type="url" value="{{ old('website', $store->website) }}" class="input" placeholder="https://">
                <x-field-error name="website" />
            </div>
            <div>
                <label for="category" class="label">Category</label>
                <select id="category" name="category" class="input">
                    <option value="">Choose a category</option>
                    @foreach (config('kustore.categories') as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $store->category) === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                <x-field-error name="category" />
            </div>
            <div>
                <label for="account_type" class="label">Account type</label>
                <select id="account_type" name="account_type" class="input">
                    @foreach (config('kustore.account_types') as $value => $label)
                        <option value="{{ $value }}" @selected(old('account_type', $store->account_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <p class="label">Username</p>
                <p class="text-sm muted">kustore.id/<span class="font-medium text-navy dark:text-white">{{ $store->username }}</span> · Usernames can't be changed yet.</p>
            </div>
        </div>
    </section>

    <section class="card card-pad">
        <h2 class="text-base font-semibold">Appearance</h2>
        <fieldset class="mt-5">
            <legend class="label">Default colour mode</legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (['light' => ['Light', 'sun'], 'dark' => ['Dark', 'moon']] as $value => [$label, $ic])
                    <label class="choice flex-row items-center gap-3">
                        <input type="radio" name="color_mode" value="{{ $value }}" @checked(old('color_mode', $store->color_mode) === $value)>
                        {{ icon($ic, 'size-[18px]') }}<span class="font-display font-medium">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <p class="help">Visitors can still switch modes on your page.</p>
        </fieldset>
        <fieldset class="mt-6">
            <legend class="label">Layout</legend>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'minimal' => 'Centered profile and links. Great for creators.',
                    'commerce' => 'Products first, wide grid. Great for shops.',
                    'creator' => 'Uses Minimal for now.',
                    'professional' => 'Uses Commerce for now.',
                    'editorial' => 'Uses Commerce for now.',
                ] as $value => $text)
                    <label class="choice">
                        <input type="radio" name="layout" value="{{ $value }}" @checked(old('layout', $store->layout) === $value)>
                        <span class="font-display font-semibold">{{ config('kustore.layouts.'.$value) }}</span>
                        <span class="text-sm muted">{{ $text }}</span>
                    </label>
                @endforeach
            </div>
            <x-field-error name="layout" />
        </fieldset>
    </section>

    <section class="card card-pad">
        <h2 class="text-base font-semibold">Payment instructions</h2>
        <p class="mt-1 text-sm muted">Shown to customers right after they place an order. Payments are manual for now.</p>
        <textarea id="payment_instructions" name="payment_instructions" rows="5" maxlength="2000" class="input mt-4" placeholder="Transfer to BCA 1234567890 a.n. Your Name, then send proof via WhatsApp 08xx with your order number.">{{ old('payment_instructions', $store->payment_instructions) }}</textarea>
        <x-field-error name="payment_instructions" />
    </section>

    <section class="card card-pad">
        <h2 class="text-base font-semibold">Search and sharing</h2>
        <p class="mt-1 text-sm muted">How your page appears on Google and when shared. Leave empty to use your name and bio.</p>
        <div class="mt-5 space-y-5">
            <div>
                <label for="seo_title" class="label">Page title</label>
                <input id="seo_title" name="seo_title" value="{{ old('seo_title', $store->seo_title) }}" maxlength="70" class="input" placeholder="{{ $store->metaTitle() }}">
                <x-field-error name="seo_title" />
            </div>
            <div>
                <label for="seo_description" class="label">Description</label>
                <textarea id="seo_description" name="seo_description" maxlength="160" rows="2" class="input min-h-20" placeholder="{{ $store->metaDescription() }}">{{ old('seo_description', $store->seo_description) }}</textarea>
                <x-field-error name="seo_description" />
            </div>
        </div>
    </section>

    <div class="sticky bottom-20 z-10 flex justify-end lg:bottom-6">
        <button class="btn btn-primary btn-lg shadow-lift">Save changes</button>
    </div>
</form>
@endsection
