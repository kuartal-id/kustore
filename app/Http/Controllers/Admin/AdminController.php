<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.index', [
            'stores' => Store::with('user')->latest()->paginate(25, ['*'], 'stores'),
            'users' => User::with('store')->latest()->paginate(25, ['*'], 'users'),
        ]);
    }

    public function toggleSuspend(Request $request, Store $store): RedirectResponse
    {
        $this->authorize('suspend', $store);
        $store->forceFill([
            'is_suspended' => ! $store->is_suspended,
            'suspended_at' => $store->is_suspended ? null : now(),
        ])->save();

        return back()->with('status', $store->is_suspended ? "@{$store->username} suspended." : "@{$store->username} restored.");
    }
}
