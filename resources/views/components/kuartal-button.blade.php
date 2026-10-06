@props(['label' => 'Continue with Kuartal ID', 'size' => 'lg'])
<a href="{{ route('auth.kuartal.redirect') }}" {{ $attributes->class(['btn btn-primary', 'btn-lg' => $size === 'lg']) }}>
    <span class="grid size-5 place-items-center rounded-md bg-green font-display text-[11px] font-bold text-ink">K</span>
    {{ $label }}
</a>
