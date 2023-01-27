<?php


namespace Elbytes\NovaTwoFactor;

use Exception;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use PragmaRX\Google2FALaravel\Support\Constants;

class TwoFaAuthenticator extends Authenticator
{
    protected function canPassWithoutCheckingOTP()
    {
        return
            !$this->isEnabled() ||
            $this->noUserIsAuthenticated() ||
            $this->twoFactorAuthStillValid();
    }

    protected function twoFactorAuthStillValid()
    {
        return
            (bool) Cache::get(Constants::SESSION_AUTH_PASSED . '_user_' . auth()->id(), false) &&
            !$this->passwordExpired();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function getGoogle2FASecretKey()
    {
        $secret = $this->getUser()->twoFa->google2fa_secret;
        if (is_null($secret) || empty($secret)) {
            throw new Exception('Secret key cannot be empty.');
        }

        return $secret;
    }
}
