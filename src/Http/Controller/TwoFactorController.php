<?php

namespace Elbytes\NovaTwoFactor\Http\Controller;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA as G2fa;
use Elbytes\NovaTwoFactor\Models\TwoFa;
use Elbytes\NovaTwoFactor\TwoFaAuthenticator;

class TwoFactorController extends Controller
{
    public function registerUser()
    {

        if(auth()->user()->twoFa && auth()->user()->twoFa->confirmed == 1){
            return response()->json([
                'message' => 'Already verified !'
            ]);
        }

        $google2fa = new G2fa();
        $secretKey = $google2fa->generateSecretKey();

        $recoveryKey = strtoupper(Str::random(16));
        $recoveryKey = str_split($recoveryKey, 4);
        $recoveryKey = implode("-", $recoveryKey);

        $recoveryKeyHashed = bcrypt($recoveryKey);

        $data['recovery'] = $recoveryKey;


        $userTwoFa                 = new TwoFa();
        $userTwoFa::where('user_id', auth()->user()->id)->delete();
        $user2fa                   = new $userTwoFa();
        $user2fa->user_id          = auth()->user()->id;
        $user2fa->type             = 'google';
        $user2fa->google2fa_secret = $secretKey;
        $user2fa->recovery         = $recoveryKeyHashed;
        $user2fa->save();



        $google2fa_url = $this->getQRCodeGoogleUrl(
            config('app.name'),
            auth()->user()->email,
            $secretKey,
            500
        );

        $data['google2fa_url'] = $google2fa_url;

        return $data;
    }

    public function verifyOtp()
    {
        $type = request()->get('type');
        $otp  = request()->get('otp');

        if ($type === 'google') {
            request()->merge(['one_time_password' => $otp]);

            $authenticator = app(TwoFaAuthenticator::class)->boot(request());

            if ($authenticator->isAuthenticated()) {
                // otp auth success!

                auth()->user()->twoFa()->update([
                    'twofa_enabled'    => true,
                    'confirmed'        => true,
                    'google2fa_enable' => true
                ]);

                return response()->json([
                    'message' => '2FA security successfully activated !'
                ]);
            }
        }

        if ($type === 'email') {
            $userTwoFa = TwoFa::where('user_id', auth()->id())
                              ->where('email_code', $otp)
                              ->where('email_updated_at', '>=', now()->subMinutes(2))
                              ->first();

            if (!is_null($userTwoFa)) {
                auth()->user()->twoFa()->update([
                    'twofa_enabled'    => true,
                    'confirmed'        => true,
                ]);

                return response()->json([
                    'message' => '2FA security successfully activated !'
                ]);
            }
        }

        // auth fail
        return response()->json([
            'message' => 'Invalid OTP !. Please try again'
        ], 422);
    }

    public function toggle2Fa(Request $request)
    {
        $status = $request->get('status') === 1;
        auth()->user()->twoFa()->update([
            'twofa_enabled'    => $status,
            'google2fa_enable' => $status
        ]);

        return response()->json([
            'message' => $status ? '2FA feature enabled!' : '2FA feature disabled !'
        ]);
    }

    public function reset2Fa()
    {
        auth()->user()->twoFa()->delete();

        return response()->json([
            'message' => '2FA has been disabled!',
        ]);
    }

    public function getStatus()
    {
        $user = auth()->user();

        $res = [
            "registered" => !empty($user->twoFa),
            "enabled"    => auth()->user()->twoFa->twofa_enabled ?? false,
            "confirmed"  => auth()->user()->twoFa->confirmed ?? false
        ];
        return $res;
    }

    public function getQRCodeGoogleUrl($company, $holder, $secret, $size = 200)
    {
        $g2fa = new G2fa();
        $url = $g2fa->getQRCodeUrl($company, $holder, $secret);

        return self::generateGoogleQRCodeUrl(
            'https://chart.googleapis.com/',
            'chart',
            'chs='.$size.'x'.$size.'&chld=M|0&cht=qr&chl=',
            $url
        );
    }

    public static function generateGoogleQRCodeUrl($domain, $page, $queryParameters, $qrCodeUrl)
    {
        $url = $domain.
            rawurlencode($page).
            '?'.$queryParameters.
            urlencode($qrCodeUrl);

        return $url;
    }

    // Form uses

    public function authenticate(Request $request)
    {
        $request->validate([
            'one_time_password'=>'required',
        ]);

        if (auth()->user()->twoFa->type === 'google') {
            $authenticator = app(TwoFaAuthenticator::class)->boot(request());

            if ($authenticator->isAuthenticated()) {
                return redirect()->to(config('nova.path'));
            }
        }

        if (auth()->user()->twoFa->type === 'email') {
            $code = TwoFa::where('user_id', auth()->id())
                            ->where('email_code', $request->one_time_password)
                            ->where('email_updated_at', '>=', now()->subMinutes(2))
                            ->first();

            if (!is_null($code)) {
                Session::put('user_email_2fa', auth()->id());
                return redirect()->to(config('nova.path'));
            }
        }

        return back()->withErrors(['Incorrect OTP !']);
    }

    public function recover(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('nova-two-factor::recover');
        }

        if (Hash::check($request->get('recovery_code'), auth()->user()->twoFa->recovery)) {
            // reset 2fa
            auth()->user()->twoFa()->delete();
            return redirect()->to(config('nova.path'));
        } else {
            return back()->withErrors(['Incorrect recovery code !']);
        }
    }

    public function sendOtpEmail()
    {
        auth()->user()->generateEmailCode();

        return response()->json([
            'message' => 'We sent code on your email.'
        ]);
    }

    public function resendEmail()
    {
        auth()->user()->generateEmailCode();

        return back()->with(['message' => 'We sent code on your email.']);
    }
}
