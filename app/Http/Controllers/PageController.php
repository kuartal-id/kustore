<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'demo' => Store::visible()->where('username', 'demo')->first(),
            // The hero example is Diemas Hawkins (@diemas). Once that store is live, the card links to it.
            'featured' => Store::visible()->where('username', 'diemas')->first(),
        ]);
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function support(): View
    {
        return view('pages.support');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }
}
