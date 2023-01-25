<?php

namespace Elbytes\NovaTwoFactor\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Send2FACodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('One time password'))
                    ->markdown('nova-two-factor::mail.2fa-code', [
                        'code' => $this->details['code'],
                    ]);
    }
}
