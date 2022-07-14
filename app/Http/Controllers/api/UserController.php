<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register_method(Request $request) {

        $validation = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        if( $validation->fails() ){
            return response()->json([
                'success' => false,
                'messgage' => $validation->errors()
            ], 400);
        }else{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 201);
        }

    }

    public function login_method(Request $request) {

        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6'
        ]);

        if( $validation->fails() ) {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()
            ], 400);
        }else{
            $user = User::where('email', $request->email)->first(); //dd($user);
            if( !$user || !Hash::check($request->password, $user->password) ){
                return response()->json([
                'success' => false,
                    'message' => 'Bad credentials'
                ], 400);
            }

            $token = $user->createToken('app-token')->plainTextToken;

            /*
            * Test notification
            */
            // $data = ['message' => 'This is test welcome message'];
            // Mail::to($user->email, $user->name)->send(new TestEmail($data));

            return response()->json([
                'success' => true,
                'token' => $token
            ], 202);
        }
    }

    public function forget_password_method(Request $request) {

        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if( $validation->fails() ) {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()
            ], 400);
        }else{
            // AuthServiceProvider -> boot() method to customie reset password link
            $status = Password::sendResetLink($request->only('email'));
            return $status === Password::RESET_LINK_SENT ? response()->json(['success' => true, 'message' => __($status)], 200) : response()->json(['success' => false, 'message' => __($status)], 400);
        }
    }

    public function reset_password_method(Request $request) {
        
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6'
        ]);

        if( $validation->fails() ) {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()
            ], 400);
        }else{
            $status = Password::reset(
                $request->only('email', 'token', 'password'),
                function($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PASSWORD_RESET ? response()->json(['success' => true, 'message' => __($status)], 200) : response()->json(['success' => false, 'message' => [__($status)] ], 400);
        }
        
    }
}
