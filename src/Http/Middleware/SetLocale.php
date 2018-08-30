<?php

namespace SuperV\Platform\Http\Middleware;

use Closure;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('locale')) {
            $selectedLocale = $request->get('locale');
            $request->session()->put('locale', $selectedLocale);

            app()->setLocale($selectedLocale);

            return redirect()->to($request->getPathInfo());
        } elseif (session()->has('locale')) {
            $selectedLocale = session('locale');
            app()->setLocale($selectedLocale);
        }

        return $next($request);
    }
}
