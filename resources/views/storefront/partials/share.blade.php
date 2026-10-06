@php
    $shareUrl = $url;
    $text = $text ?? $store->display_name.' on Kustore';
    $targets = [
        ['whatsapp', 'WhatsApp', 'https://wa.me/?text='.rawurlencode($text.' '.$shareUrl)],
        ['x', 'X', 'https://x.com/intent/post?text='.rawurlencode($text).'&url='.rawurlencode($shareUrl)],
        ['facebook', 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($shareUrl)],
        ['linkedin', 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($shareUrl)],
        ['telegram', 'Telegram', 'https://t.me/share/url?url='.rawurlencode($shareUrl).'&text='.rawurlencode($text)],
    ];
@endphp
<div id="share" class="scroll-mt-20">
    <p class="eyebrow">Share</p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ $shareUrl }}">{{ icon('copy', 'size-4') }}<span data-copy-label>Copy link</span></button>
        @foreach ($targets as [$icon, $label, $href])
            <a href="{{ $href }}" target="_blank" rel="noopener nofollow" class="btn btn-secondary btn-icon size-9" aria-label="Share on {{ $label }}" title="{{ $label }}">{{ icon($icon, 'size-4') }}</a>
        @endforeach
    </div>
</div>
