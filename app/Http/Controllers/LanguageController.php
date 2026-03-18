<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    public function switch(string $locale): RedirectResponse
    {
        $supported = config('app.supported_locales', ['es', 'en', 'va']);

        if (! in_array($locale, $supported, true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        return back();
    }
}