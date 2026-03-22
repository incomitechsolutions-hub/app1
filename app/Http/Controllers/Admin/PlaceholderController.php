<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaceholderController extends Controller
{
    public function __invoke(Request $request): View
    {
        $title = (string) $request->route()->parameter('title', __('Bereich'));

        return view('admin.placeholder', ['title' => $title]);
    }
}
