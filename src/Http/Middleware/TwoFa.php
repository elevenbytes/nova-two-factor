<?php


namespace Elbytes\NovaTwoFactor\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Session;
use Elbytes\NovaTwoFactor\TwoFaAuthenticator;

class TwoFa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \PragmaRX\Google2FA\Exceptions\InsecureCallException
     */
    public function handle($request, Closure $next)
    {

        $except = [
            'nova-vendor/nova-two-factor/authenticate',
            'nova-vendor/nova-two-factor/recover',
            'nova-vendor/nova-two-factor/resend-code',
            'logout'
        ];

        $except = array_merge($except,config('nova-two-factor.excect_routes'));

        if (!config('nova-two-factor.enabled') || in_array($request->path(),$except)) {
            return $next($request);
        }

        // turn off security if no user2fa record
        if( auth()->guest() || !auth()->user()->twoFa){
            return $next($request);
        }

        // turn off security if 2fa is off
        if(auth()->user()->twoFa && ! auth()->user()->twoFa->twofa_enabled){
            return $next($request);
        }

        // Wait Google code for "google_2fa" type.
        if( auth()->user()->twoFa->type === 'google' )  {
            $authenticator = app(TwoFaAuthenticator::class)->boot($request);
            if (! $authenticator->isAuthenticated()) {
                return response(view('nova-two-factor::sign-in'));
            }
        }

        // Send Email for "email_2fa" type.
        if( auth()->user()->twoFa->type === 'email' )  {
            if (!Session::has('user_email_2fa') ) {
                if(auth()->user()->twoFa->email_updated_at < now()->subMinutes(2)) {
                    auth()->user()->generateEmailCode();
                }
                return response(view('nova-two-factor::sign-in'));
            }
        }

        return $next($request);
    }

}
