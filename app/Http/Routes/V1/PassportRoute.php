<?php
namespace App\Http\Routes\V1;

use App\Http\Controllers\V1\Passport\AuthController;
use App\Http\Controllers\V1\Passport\CommController;
use App\Http\Controllers\V1\Passport\LogtoAuthController;
use Illuminate\Contracts\Routing\Registrar;

class PassportRoute
{
    public function map(Registrar $router)
    {
        $router->group([
            'prefix' => 'passport'
        ], function ($router) {
            // Logto Authentication (Primary) - with configuration check
            $router->group([
                'middleware' => 'logto.configured'
            ], function ($router) {
                $router->get('/auth/logto/sign-in', [LogtoAuthController::class, 'signIn']);
                $router->get('/auth/logto/callback', [LogtoAuthController::class, 'callback']);
                $router->post('/auth/logto/sign-out', [LogtoAuthController::class, 'signOut']);
                $router->get('/auth/logto/userinfo', [LogtoAuthController::class, 'userInfo']);
                $router->get('/auth/logto/check', [LogtoAuthController::class, 'checkAuth']);
            });
            
            // Comm (Keep for other features)
            $router->post('/comm/pv', [CommController::class, 'pv']);
        });
    }
}
