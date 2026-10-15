@extends('layouts.dashboard')
@section('page_title', 'Settings')
@section('page_subtitle', 'Your account, username and data.')

@section('content')
<div class="max-w-2xl space-y-4 sm:space-y-6">
    <section class="card card-pad">
        <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('user', 'size-[18px]') }} Account</h2>
        <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
            <div><dt class="muted">Signed in with</dt><dd class="mt-0.5 font-medium">{{ $user->isKuartalIdUser() ? 'Kuartal ID' : 'Email and password' }}</dd></div>
            <div><dt class="muted">Email</dt><dd class="mt-0.5 font-medium break-all">{{ $user->email ?: '—' }}</dd></div>
        </dl>
        <form method="POST" action="{{ route('dashboard.settings.profile') }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf @method('PUT')
            <div class="flex-1">
                <label for="name" class="label">Your name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required autocomplete="name" class="input">
                <x-field-error name="name" />
            </div>
            <button class="btn btn-secondary">Save name</button>
        </form>
        @if ($user->isKuartalIdUser())
            <p class="help mt-3">Your email and sign-in security are managed by Kuartal ID at <a href="https://id.kuartal.id" class="link" rel="noopener">id.kuartal.id</a>.</p>
        @endif
    </section>

    <section class="card card-pad">
        <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('link-2', 'size-[18px]') }} Username</h2>
        <p class="mt-1 text-sm muted">Your page address. If you change it, the old link stops working, so update it wherever you shared it.</p>
        <form method="POST" action="{{ route('dashboard.settings.username') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf @method('PUT')
            <div class="flex-1">
                <label for="username" class="label">Username</label>
                <div class="input-group">
                    <span>kustore.id/</span>
                    <input id="username" name="username" value="{{ old('username', $store->username) }}" maxlength="30" required autocomplete="off" autocapitalize="none" spellcheck="false" data-username>
                </div>
                <x-field-error name="username" />
            </div>
            <button class="btn btn-secondary">Change username</button>
        </form>
    </section>

    @unless ($user->isKuartalIdUser())
        <section class="card card-pad">
            <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('key-round', 'size-[18px]') }} Password</h2>
            <form method="POST" action="{{ route('dashboard.settings.password') }}" class="mt-4 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label for="current_password" class="label">Current password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input">
                    <x-field-error name="current_password" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="label">New password</label>
                        <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password" class="input">
                        <x-field-error name="password" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Repeat new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="10" autocomplete="new-password" class="input">
                    </div>
                </div>
                <p class="help">At least 10 characters, with letters and numbers. Changing it signs you out on your other devices.</p>
                <button class="btn btn-secondary">Change password</button>
            </form>
        </section>
    @endunless

    <section class="card card-pad">
        <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('download', 'size-[18px]') }} Your data</h2>
        <p class="mt-1 text-sm muted">Download everything Kustore holds for your account: profile, links, products, customers and orders, as a JSON file.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('dashboard.settings.export') }}" class="btn btn-secondary btn-sm">{{ icon('download', 'size-4') }} Download my data</a>
            <a href="{{ route('dashboard.customers.export') }}" class="btn btn-ghost btn-sm">Customers as CSV</a>
        </div>
    </section>

    <section class="card card-pad border-danger/30 dark:border-[#FF8A75]/25">
        <h2 class="flex items-center gap-2 text-base font-semibold text-danger dark:text-[#FF8A75]">{{ icon('trash-2', 'size-[18px]') }} Delete account</h2>
        <p class="mt-1 text-sm muted">Permanently deletes your Kustore page, links, products, photos, orders and customer list. This can't be undone. Download your data first if you need it.</p>
        @if ($user->isKuartalIdUser())
            <p class="mt-2 text-sm muted">This deletes your Kustore account only. To delete your Kuartal ID itself, go to <a href="https://id.kuartal.id/account/delete" class="link" rel="noopener">id.kuartal.id/account/delete</a>.</p>
        @endif
        <form method="POST" action="{{ route('dashboard.settings.destroy') }}" class="mt-4 space-y-3">
            @csrf @method('DELETE')
            <label for="confirm" class="label">Type <strong>{{ $store->username }}</strong> to confirm</label>
            <input id="confirm" name="confirm" autocomplete="off" autocapitalize="none" spellcheck="false" class="input" data-confirm-text="{{ $store->username }}" data-confirm-target="delete-account-button">
            <x-field-error name="confirm" />
            <button id="delete-account-button" class="btn btn-danger">Delete my account</button>
        </form>
    </section>
</div>
@endsection
