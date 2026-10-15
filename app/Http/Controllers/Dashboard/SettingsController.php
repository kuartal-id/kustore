<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Rules\Username;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Account settings: name, username, password, data export and account deletion. */
class SettingsController extends Controller
{
    public function show(Request $request): View
    {
        return view('dashboard.settings', ['store' => $request->user()->store, 'user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $request->user()->forceFill(['name' => $data['name']])->save();

        return back()->with('status', 'Name saved.');
    }

    public function updateUsername(Request $request): RedirectResponse
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        $request->merge(['username' => strtolower(trim((string) $request->input('username')))]);
        $data = $request->validate([
            'username' => ['required', 'string', new Username, Rule::unique('stores', 'username')->ignore($store->id)],
        ], ['username.unique' => 'That username is already taken.']);

        if ($data['username'] === $store->username) {
            return back()->with('status', 'That is already your username.');
        }

        $store->forceFill(['username' => $data['username']])->save();

        return back()->with('status', 'Username changed. Your page is now at kustore.id/'.$store->username.'. Update the link anywhere you shared the old one.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->isKuartalIdUser(), 404);

        $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();
        // Sign out other devices that used the old password.
        Auth::logoutOtherDevices($request->input('password'));

        return back()->with('status', 'Password changed.');
    }

    /** Everything Kustore holds about the account, as JSON. */
    public function export(Request $request): Response
    {
        $user = $request->user();
        $store = $user->store()->with([
            'links',
            'products.images',
            'customers',
            'orders.items',
            'orders.payments',
        ])->first();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'sign_in' => $user->isKuartalIdUser() ? 'Kuartal ID' : 'Email and password',
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'store' => $store?->only([
                'username', 'display_name', 'bio', 'location', 'website', 'category', 'account_type', 'layout',
                'color_mode', 'seo_title', 'seo_description', 'currency', 'payment_instructions', 'whatsapp_number',
                'is_published', 'published_at', 'created_at',
            ]),
            'links' => $store?->links->map->only(['title', 'url', 'icon', 'is_visible', 'position', 'clicks_count'])->values(),
            'products' => $store?->products->map(fn ($p) => $p->only([
                'name', 'slug', 'description', 'price', 'sale_price', 'currency', 'type', 'sku', 'stock_quantity',
                'unlimited_stock', 'is_active', 'is_featured', 'requires_shipping', 'created_at',
            ]) + ['images' => $p->images->map->url()->values()])->values(),
            'customers' => $store?->customers->map->only(['name', 'email', 'phone', 'created_at'])->values(),
            'orders' => $store?->orders->map(fn ($o) => $o->only([
                'number', 'customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'notes', 'currency',
                'subtotal', 'shipping_total', 'total', 'payment_provider', 'payment_status', 'fulfillment_status',
                'paid_at', 'created_at',
            ]) + ['items' => $o->items->map->only(['product_name', 'product_type', 'unit_price', 'quantity', 'line_total'])->values()])->values(),
        ];

        $name = 'kustore-export-'.($store?->username ?? 'account').'-'.now()->format('Ymd').'.json';

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** Permanently deletes the Kustore account, store and everything in it. */
    public function destroy(Request $request, ImageUploader $uploader): RedirectResponse
    {
        $user = $request->user();
        $store = $user->store;

        $request->validate([
            'confirm' => ['required', 'string', Rule::in([$store?->username ?? 'DELETE'])],
        ], ['confirm.in' => 'Type your username exactly to confirm.']);

        // Sign out first: logging out writes the remember token, which would re-insert a deleted user row.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        DB::transaction(function () use ($user, $store, $uploader) {
            if ($store) {
                $uploader->delete($store->avatar_path);
                $uploader->delete($store->payment_qris_path);
                foreach ($store->products()->with('images')->get() as $product) {
                    foreach ($product->images as $image) {
                        $uploader->delete($image->path);
                    }
                }
                $store->delete(); // links, products, orders, customers, analytics cascade
            }
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->delete();
        });

        return redirect()->route('home')->with('status', 'Your Kustore account has been deleted.');
    }
}
