<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * First-party analytics. Never stores raw IPs or third-party identifiers.
 */
class Analytics
{
    public function record(Request $request, Store $store, string $type, ?int $linkId = null, ?int $productId = null): void
    {
        if ($this->shouldSkip($request, $store)) {
            return;
        }

        try {
            AnalyticsEvent::create([
                'store_id' => $store->id,
                'type' => $type,
                'store_link_id' => $linkId,
                'product_id' => $productId,
                'visitor_hash' => $this->visitorHash($request),
                'referrer_host' => $this->referrerHost($request),
            ]);
        } catch (\Throwable $e) {
            // Analytics must never break a page.
            Log::warning('analytics.record_failed', ['message' => $e->getMessage()]);
        }
    }

    /** Daily-rotating hash: cannot be reversed to an IP and cannot follow a visitor across days. */
    public function visitorHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            (string) $request->ip(),
            (string) $request->userAgent(),
            now()->toDateString(),
            (string) config('app.key'),
        ]));
    }

    private function referrerHost(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return $host ? substr(strtolower($host), 0, 255) : null;
    }

    private function shouldSkip(Request $request, Store $store): bool
    {
        // Don't count the owner looking at their own store.
        if ($request->user() && $request->user()->id === $store->user_id) {
            return true;
        }

        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless/i', (string) $request->userAgent());
    }
}
