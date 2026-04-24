<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BackUrlResolver
{
    public function resolveForLogShow(Request $request, int $id): string
    {
        $fallback = route('logs.index');
        $sessionKey = "navigation.logs.show.$id.back";
        $referer = $request->headers->get('referer');

        if (is_string($referer) && Str::startsWith($referer, url('/')) && self::isListIndexUrl($referer, 'logs.index')) {
            $request->session()->put($sessionKey, $referer);
        }

        $stored = $request->session()->get($sessionKey);

        return is_string($stored) && Str::startsWith($stored, url('/')) ? $stored : $fallback;
    }

    public function resolveForArchivedShow(Request $request, int $id): string
    {
        $fallback = route('archived-logs.index');
        $sessionKey = "navigation.archived.show.$id.back";
        $referer = $request->headers->get('referer');

        if (is_string($referer) && Str::startsWith($referer, url('/'))) {
            if (self::isListIndexUrl($referer, 'archived-logs.index')) {
                $request->session()->put($sessionKey, $referer);
            }

            if (self::isActiveLogDetailUrl($referer)) {
                return $referer;
            }
        }

        $stored = $request->session()->get($sessionKey);

        return is_string($stored) && Str::startsWith($stored, url('/')) ? $stored : $fallback;
    }

    /**
     * True if the URL is the list index (same prefix as the named route) but not a resource under it (e.g. /logs vs /logs/1).
     */
    public static function isListIndexUrl(string $url, string $indexRouteName): bool
    {
        $indexPrefix = route($indexRouteName);
        $showPrefix = $indexPrefix.'/';

        return Str::startsWith($url, $indexPrefix) && ! Str::startsWith($url, $showPrefix);
    }

    public static function isActiveLogDetailUrl(string $url): bool
    {
        $showPrefix = route('logs.index').'/';

        return Str::startsWith($url, $showPrefix);
    }
}
