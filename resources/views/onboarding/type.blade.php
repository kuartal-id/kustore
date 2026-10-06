@extends('layouts.narrow')
@section('title', 'Account type · Kustore')
@section('noindex', true)
@section('width', 'max-w-lg')

@section('content')
<p class="eyebrow">Step 2 of 2</p>
<h1 class="mt-3 text-3xl font-semibold">Who is this Kustore for?</h1>
<p class="mt-2 muted">kustore.id/<span class="font-medium text-navy dark:text-white">{{ $username }}</span> · <a href="{{ route('onboarding.username') }}" class="link">change</a></p>

<form method="POST" action="{{ route('onboarding.type') }}" class="card card-pad mt-8 space-y-6 sm:p-8">
    @csrf
    <x-field-error name="username" />
    <fieldset>
        <legend class="label">Account type</legend>
        <div class="grid gap-3">
            @foreach ([
                'individual' => ['Individual', 'Creators, freelancers and personal brands.'],
                'business' => ['Business', 'Shops, brands and small companies.'],
                'organisation' => ['Organisation', 'Communities, schools, nonprofits and teams.'],
            ] as $value => [$title, $text])
                <label class="choice">
                    <input type="radio" name="account_type" value="{{ $value }}" @checked(old('account_type', 'individual') === $value)>
                    <span class="font-display font-semibold">{{ $title }}</span>
                    <span class="text-sm muted">{{ $text }}</span>
                </label>
            @endforeach
        </div>
        <x-field-error name="account_type" />
    </fieldset>
    <div>
        <label for="display_name" class="label">Display name</label>
        <input id="display_name" name="display_name" value="{{ old('display_name', $name) }}" required maxlength="80" class="input">
        <x-field-error name="display_name" />
        <p class="help">Shown at the top of your page. You can change it later.</p>
    </div>
    <button class="btn btn-primary btn-lg w-full">Create my Kustore</button>
</form>
@endsection
