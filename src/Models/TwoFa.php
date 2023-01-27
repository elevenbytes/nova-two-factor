<?php

namespace Elbytes\NovaTwoFactor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFa extends Model
{
    protected $table = 'nova_twofa';


    public function user(): BelongsTo
    {
        return $this->belongsTo(config('nova-two-factor.user_model'));
    }
}
