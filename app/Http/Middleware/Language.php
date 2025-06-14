<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class Language
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lang = $request->get('lang', 'en');
        session()->put('lang', $lang);
        //$lang = session('lang', 'zh');
        App::setLocale($lang);
        
        return $next($request);
    }

}