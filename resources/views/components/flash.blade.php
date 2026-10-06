@if (session('status'))
    <div {{ $attributes->class(['flash flash-ok']) }} role="status">{{ icon('check', 'size-4 mt-0.5 shrink-0') }}<span>{{ session('status') }}</span></div>
@endif
@if ($errors->any() && ! ($fieldsOnly ?? false))
    <div {{ $attributes->class(['flash flash-error']) }} role="alert">{{ icon('circle-alert', 'size-4 mt-0.5 shrink-0') }}
        <div>@if ($errors->count() === 1){{ $errors->first() }}@else Please check the highlighted fields.@endif</div>
    </div>
@endif
