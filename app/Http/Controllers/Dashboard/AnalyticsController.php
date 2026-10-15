<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * First-party numbers only (see App\Services\Analytics): page views, unique visitors
 * (daily-rotating hash, so "visitors" means distinct per day), link clicks, product views,
 * referrers, and orders/sales from the store's own orders.
 */
class AnalyticsController extends Controller
{
    public const RANGES = [7, 30, 90];

    public function index(Request $request): View
    {
        $store = $request->user()->store;
        $this->authorize('view', $store);

        $days = (int) $request->query('days', 30);
        if (! in_array($days, self::RANGES, true)) {
            $days = 30;
        }
        $today = CarbonImmutable::today();
        $from = $today->subDays($days - 1);

        $events = $store->analyticsEvents()->where('created_at', '>=', $from);

        // Daily rows: date, type, count, distinct visitors. DATE() works in MySQL and SQLite.
        $daily = (clone $events)
            ->selectRaw('DATE(created_at) as d, type, COUNT(*) as c, COUNT(DISTINCT visitor_hash) as v')
            ->groupBy('d', 'type')
            ->get();

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $key = $from->addDays($i)->toDateString();
            $series[$key] = ['date' => $from->addDays($i), 'views' => 0, 'visitors' => 0, 'clicks' => 0, 'product_views' => 0];
        }
        foreach ($daily as $row) {
            $key = substr((string) $row->d, 0, 10);
            if (! isset($series[$key])) {
                continue;
            }
            match ($row->type) {
                AnalyticsEvent::STORE_VIEW => [$series[$key]['views'] = (int) $row->c, $series[$key]['visitors'] = (int) $row->v],
                AnalyticsEvent::LINK_CLICK => $series[$key]['clicks'] = (int) $row->c,
                AnalyticsEvent::PRODUCT_VIEW => $series[$key]['product_views'] = (int) $row->c,
                default => null,
            };
        }

        $orders = $store->orders()->where('created_at', '>=', $from);

        return view('dashboard.analytics', [
            'store' => $store,
            'days' => $days,
            'ranges' => self::RANGES,
            'series' => array_values($series),
            'totals' => [
                'views' => array_sum(array_column($series, 'views')),
                'visitors' => (clone $events)->where('type', AnalyticsEvent::STORE_VIEW)->distinct()->count('visitor_hash'),
                'clicks' => array_sum(array_column($series, 'clicks')),
                'product_views' => array_sum(array_column($series, 'product_views')),
                'orders' => (clone $orders)->count(),
                'sales' => (int) (clone $orders)->paid()->sum('total'),
            ],
            'topLinks' => (clone $events)->where('type', AnalyticsEvent::LINK_CLICK)->whereNotNull('store_link_id')
                ->selectRaw('store_link_id, COUNT(*) as c')->groupBy('store_link_id')->orderByDesc('c')->limit(8)
                ->with('link:id,title,url')->get(),
            'topProducts' => (clone $events)->where('type', AnalyticsEvent::PRODUCT_VIEW)->whereNotNull('product_id')
                ->selectRaw('product_id, COUNT(*) as c')->groupBy('product_id')->orderByDesc('c')->limit(8)
                ->with('product:id,name,slug')->get(),
            'referrers' => (clone $events)->where('type', AnalyticsEvent::STORE_VIEW)
                ->selectRaw("COALESCE(referrer_host, '') as host, COUNT(*) as c")->groupBy('host')->orderByDesc('c')->limit(8)->get(),
        ]);
    }
}
