![screenshot](/resources/img/nova-two-factor-banner.png)

<!-- [![Latest Stable Version](http://poser.pugx.org/visanduma/nova-two-factor/v)](https://packagist.org/packages/visanduma/nova-two-factor) [![Total Downloads](http://poser.pugx.org/visanduma/nova-two-factor/downloads)](https://packagist.org/packages/visanduma/nova-two-factor) [![Latest Unstable Version](http://poser.pugx.org/visanduma/nova-two-factor/v/unstable)](https://packagist.org/packages/visanduma/nova-two-factor) [![License](http://poser.pugx.org/visanduma/nova-two-factor/license)](https://packagist.org/packages/visanduma/nova-two-factor) [![PHP Version Require](http://poser.pugx.org/visanduma/nova-two-factor/require/php)](https://packagist.org/packages/visanduma/nova-two-factor) -->

# Nova Two Factor
Laravel nova in-dashboard 2FA security feature including email OPT and Google App codes. Fork of visanduma/nova-two-factor package.

## Install
```
composer install elbytes/nova-two-factor
```

## Look

Select type (email/Google app) and setup 2FA

![screenshot](/resources/img/sc-1.png)

Enable/Disable feature

![screenshot](/resources/img/sc-2.png)

Nova login screen with 2FA security

![screenshot](/resources/img/sc-3.png)

1. Pubish config & migration

`` php artisan vendor:publish --provider="Elbytes\NovaTwoFactor\ToolServiceProvider" ``


Change configs as your needs

``` 

return [
    // enable or disale 2FA feature. default is enabled
    'enabled' => env('NOVA_TWO_FA_ENABLE',true),
    
    // name of authenticatable entity table. usually - users
    'user_table' => 'users',
    
    // Entity primary key
    'user_id_column' => 'id',
    
    // authenticatable model class
    'user_model' => \App\Models\User::class

];

```


2. Use ProtectWith2FA trait in configured model

``` 
<?php

namespace App\Models;

use Elbytes\NovaTwoFactor\ProtectWith2FA;

class User extends Authenticatable{

    use ProtectWith2FA;
}

```



3. Add TwoFa middleware to nova config file


``` 
/*
    |--------------------------------------------------------------------------
    | Nova Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Nova route, giving you the
    | chance to add your own middleware to this stack or override any of
    | the existing middleware. Or, you can just stick with this stack.
    |
    */

    'middleware' => [
        ...
        \Elbytes\NovaTwoFactor\Http\Middleware\TwoFa::class
    ],

```


4. Register NovaTwoFactor tool in Nova Service Provider

``` 
<?php

class NovaServiceProvider extends NovaApplicationServiceProvider{

public function tools()
    {
        return [
            ...
            new \Elbytes\NovaTwoFactor\NovaTwoFactor()

        ];
    }

}


```

5. Run `` php artisan migrate ``
6. You are done !
