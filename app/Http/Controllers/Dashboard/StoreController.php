<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function edit(Request $request): View
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        return view('dashboard.store', ['store' => $store]);
    }

    public function update(Request $request, ImageUploader $uploader): RedirectResponse
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
            'bio' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url:http,https', 'max:2048'],
            'category' => ['nullable', Rule::in(config('kustore.categories'))],
            'account_type' => ['required', Rule::in(array_keys(config('kustore.account_types')))],
            'layout' => ['required', Rule::in(array_keys(config('kustore.layouts')))],
            'color_mode' => ['required', Rule::in(['light', 'dark'])],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'file', ...ImageUploader::rules()],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('avatar')) {
            $uploader->delete($store->avatar_path);
            $store->avatar_path = $uploader->store($request->file('avatar'), 'avatars');
        } elseif ($request->boolean('remove_avatar')) {
            $uploader->delete($store->avatar_path);
            $store->avatar_path = null;
        }

        unset($data['avatar'], $data['remove_avatar']);
        $store->fill($data)->save();

        return back()->with('status', 'Store saved.');
    }
}
