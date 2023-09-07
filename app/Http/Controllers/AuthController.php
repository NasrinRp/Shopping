<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request){
        try{
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:4'
            ]);
            $data = array(
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
            );
            $exist_user = User::where('email', $request->email)->first();
            if(!$exist_user){
                $register = User::create($data);
                if($register){
                    return response()->json([
                        "message" => "Success register user",
                        "data" => $register,
                    ], 201);
                }else{
                    return response()->json([
                        "message" => "Failed register user"
                    ], 400);
                }
            }else{
                return response()->json([
                    "message" => "Email already use, please use other email"
                ], 400);
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function login(Request $request){
        try{
            $email = $request->email;
            $password = $request->password;
            $credentials = $request->only(['email', 'password']);
            $token = auth('api')->setTTL(60)->attempt($credentials, true);
            if($token){
                $jwt = $this->respondWithToken($token);
                $user = User::where('email' ,$email)
                    ->first();
                $user->update([
                    "api_token" => $jwt->original['api_token'],
                ]);
                return response()->json([
                    "message" => 'Success Login',
                    "data" => [
                        "user" => $user,
                        "api_token" => $jwt->original
                    ]
                ], 201);
            }else{
                return response()->json([
                    "message" => 'Email or Password not found'
                ], 403);
            }
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), ]);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'api_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function checkLogin(){
        try{
            return response()->json(auth()->user());
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function logout(Request $request){
        try{
            $user = User::where('api_token', $request->bearerToken())
                ->update([
                    "api_token" => ''
                ]);
            auth()->logout();
            return response()->json([
                "message" => "success logout",
            ]);
        }catch(\Exception $e){
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
