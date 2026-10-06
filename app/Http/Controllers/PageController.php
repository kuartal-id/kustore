<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $demo = Store::visible()->where('username', 'demo')->first();

        return view('pages.home', ['demo' => $demo]);
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }
}
