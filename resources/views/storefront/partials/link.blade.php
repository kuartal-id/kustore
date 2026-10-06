<a href="{{ route('links.go', $link) }}" rel="noopener nofollow" target="_blank"
   class="group flex min-h-14 items-center gap-3 rounded-full border border-line bg-white px-4 py-3 transition-colors hover:border-navy/40 dark:border-white/[0.09] dark:bg-navy-900 dark:hover:border-white/30">
    <span class="grid size-8 shrink-0 place-items-center text-navy dark:text-white">{{ icon($link->icon, 'size-[19px]') }}</span>
    <span class="flex-1 truncate text-center font-display text-[15px] font-medium">{{ $link->title }}</span>
    <span class="grid size-8 shrink-0 place-items-center text-navy/35 transition-colors group-hover:text-navy dark:text-white/30 dark:group-hover:text-white">{{ icon('arrow-right', 'size-4') }}</span>
</a>
