<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OverviewController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        $this->authorize('view', $store);

        $since = now()->subDays(30);
        $events = $store->analyticsEvents()->where('created_at', '>=', $since);

        return view('dashboard.overview', [
            'store' => $store,
            'views' => (clone $events)->where('type', AnalyticsEvent::STORE_VIEW)->count(),
            'clicks' => (clone $events)->where('type', AnalyticsEvent::LINK_CLICK)->count(),
            'revenue' => (int) $store->orders()->paid()->sum('total'),
            'pendingOrders' => $store->orders()->where('payment_status', 'pending')->count(),
            'activeProducts' => $store->products()->active()->count(),
            'linksCount' => $store->links()->count(),
            'recentOrders' => $store->orders()->latest()->limit(5)->get(),
        ]);
    }

    public function togglePublish(Request $request): RedirectResponse
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        if ($store->is_published) {
            $store->forceFill(['is_published' => false])->save();

            return back()->with('status', 'Your Kustore is now hidden from the public.');
        }

        if (! $request->user()->canPublishStore()) {
            return redirect()->route('verification.notice')
                ->withErrors(['publish' => 'Please verify your email before publishing your Kustore.']);
        }
        $this->authorize('publish', $store);

        $store->forceFill(['is_published' => true, 'published_at' => $store->published_at ?? now()])->save();

        return back()->with('status', 'Your Kustore is live.');
    }
}
