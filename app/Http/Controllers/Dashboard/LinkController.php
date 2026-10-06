<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        return view('dashboard.links.index', ['store' => $store, 'links' => $store->links()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        $data = $this->validated($request);
        $data['position'] = (int) $store->links()->max('position') + 1;
        $store->links()->create($data);

        return back()->with('status', 'Link added.');
    }

    public function edit(Request $request, StoreLink $link): View
    {
        $this->authorize('update', $link);

        return view('dashboard.links.edit', ['store' => $request->user()->store, 'link' => $link]);
    }

    public function update(Request $request, StoreLink $link): RedirectResponse
    {
        $this->authorize('update', $link);
        $link->update($this->validated($request));

        return redirect()->route('dashboard.links.index')->with('status', 'Link saved.');
    }

    public function destroy(StoreLink $link): RedirectResponse
    {
        $this->authorize('delete', $link);
        $link->delete();

        return redirect()->route('dashboard.links.index')->with('status', 'Link removed.');
    }

    public function move(Request $request, StoreLink $link, string $direction): RedirectResponse
    {
        $this->authorize('update', $link);
        $links = $request->user()->store->links()->get()->values();
        $index = $links->search(fn ($l) => $l->id === $link->id);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && isset($links[$swap])) {
            DB::transaction(function () use ($links, $index, $swap) {
                $ordered = $links->all();
                [$ordered[$index], $ordered[$swap]] = [$ordered[$swap], $ordered[$index]];
                foreach ($ordered as $pos => $l) {
                    $l->forceFill(['position' => $pos + 1])->saveQuietly();
                }
            });
        }

        return redirect()->route('dashboard.links.index');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:2048', 'url:http,https'],
            'icon' => ['required', Rule::in(array_keys(config('kustore.link_icons')))],
            'is_visible' => ['nullable', 'boolean'],
        ]);
        $data['is_visible'] = $request->boolean('is_visible');

        return $data;
    }
}
