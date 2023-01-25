<?php


namespace Elbytes\NovaTwoFactor;

use Elbytes\NovaTwoFactor\Mail\Send2FACodeMail;
use Elbytes\NovaTwoFactor\Models\TwoFa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait ProtectWith2FA
{
    public function twoFa()
    {
        return $this->hasOne(TwoFa::class, 'user_id', config('nova-two-factor.user_id_column'));
    }

    public function generateEmailCode()
    {
        $code = rand(100000, 999999);

        $userTwoFa = TwoFa::where('user_id', auth()->id())->first();

        $userTwoFa->type             = 'email';
        $userTwoFa->email_code       = $code;
        $userTwoFa->email_updated_at = now();
        $userTwoFa->save();

        try {
            $details = [
                'code'  => $code
            ];

            Mail::to(auth()->user())->send(new Send2FACodeMail($details));
        } catch (\Exception $e) {
            Log::critical("Error: ". $e->getMessage());
        }
    }

    public function is2faRnabled()
    {
        return $this->twoFa &&
               $this->twoFa->twofa_enabled;
    }
}
