<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Access\AuthorizationException;

class EmailVerificationController extends Controller
{
    public function send_email_to_verify_method(Request $request) {
        if( $request->user()->hasVerifiedEmail() ) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 409);
        }

        $request->user()->sendEmailVerificationNotification(); // send verification notification

        return response()->json([
            'success' => true,
            'message' => 'A notification has been sent to your registered email'
        ], 200);
    }

    public function verify_method(Request $request) {

        $user = User::find($request->route('id'));

        if($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified'
            ], 409);
        }

        if( (int)$request->get('expires') > time() ) {
            return response()->json([
                'success' => false,
                'message' => 'Link expired, please try again'
            ], 400);
        }

        if ( !hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification())) ) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong, please check again'
            ], 400);
        }

        if ($user->markEmailAsVerified())
            event(new Verified($user));

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ], 200);
    }
}
