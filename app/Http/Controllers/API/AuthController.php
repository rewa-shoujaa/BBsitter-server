<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use JWTAuth;
use Auth;


class AuthController extends Controller
{

    function login(Request $request)
    {
        $data = $request->only("email", "password");

        try {
            if (!$token = JWTAuth::attempt($data)) {
                throw new Exception('Wrong Username or password.');
            }
        }
        catch (JWTException $e) {
            throw ($e);
        }

        $user = Auth::user();
        $user->token = $token;
        return json_encode($user);

    }

    function logout()
    {
        Auth::logout();
        return redirect()->route("index");

    }


    function register(Request $request)
    {
        $user = new User;
        //$user->id = uniqid();
        $user->user_type = $request->user_type;
        $user->has_details = 0;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return json_encode("Hello");
    }

}