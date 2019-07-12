<?php

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', function (Request $req) {
    $user = User::where('email', $req->email)->first();
    if(Hash::check($req->password, $user->password)){
        if ($user->api_token == null) {
            $apikey = base64_encode(str_random(40));
            User::where('email', $req->email)->update(['api_token' => "$apikey"]);
            return [
                'status' => true,
                'data' => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'api_token' => $apikey
                ]
            ];
        }
        return [
            'status' => true,
            'message' => 'Anda sudah login'
        ];
    }
    else {
        return [
            'status' => false,
            'message' => 'Gagal masuk'
        ];
    }
});

$router->post('/register', function (Request $req) {
    $user = new User;
    $user->name = $req->name;
    $user->email = $req->email;
    $user->password = Hash::make($req->password);
    if ($user->save()) {
        return [
            'status' => true,
            'message' => 'Pendaftaran berhasil'
        ];
    }
    else {
        return [
            'status' => false,
            'message' => 'Pendaftaran gagal'
        ];
    }
});

$router->get('/profile', ['middleware' => 'auth', function (Request $req) {
    return [
        'status' => true,
        'data' => Auth::user()
    ];
}]);

$router->delete('/logout', ['middleware' => 'auth', function (Request $req) {
    $user = User::find($req->user()->uuid);
    $user->api_token = null;
    if ($user->save()) {
        return [
            'status' => true,
            'message' => 'Berhasil logout'
        ];
    }
    return [
        'status' => false,
        'message' => 'Gagal melakukan logout'
    ];
}]);
