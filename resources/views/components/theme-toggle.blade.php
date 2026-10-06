<button type="button" data-theme-toggle {{ $attributes->class(['btn btn-ghost btn-icon']) }} aria-label="Switch light or dark mode" title="Light / dark">
    <span class="dark:hidden">{{ icon('moon', 'size-[18px]') }}</span>
    <span class="hidden dark:inline">{{ icon('sun', 'size-[18px]') }}</span>
</button>
