<a href="{{ $store->url() }}" class="flex min-w-0 items-center gap-2.5 rounded-full py-1 pr-3 hover:bg-navy/5 dark:hover:bg-white/5">
    <x-avatar :store="$store" size="size-8" text="text-xs" />
    <span class="truncate font-display text-sm font-medium">{{ $store->display_name }}</span>
</a>
