<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PlaceholderController extends Controller
{
    public function __invoke(string $page): View
    {
        $pages = config('navigation.pages');
        abort_unless(isset($pages[$page]), 404);
        abort_unless(in_array(request()->user()->role, $pages[$page]['roles'], true), 403, 'Anda tidak memiliki akses ke halaman ini.');
        return view('placeholder', ['page' => $pages[$page]]);
    }
}
