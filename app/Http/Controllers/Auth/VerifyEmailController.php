<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;

class VerifyEmailController extends Controller
{
    public function verify($id, $hash, $url)
    {

        $user = User::findOrFail($id);

        abort_if(!hash_equals(
            (string) $id,
            (string) $user->getKey()
        ), Response::HTTP_FORBIDDEN);

        abort_if(!hash_equals(
            (string) $hash,
            sha1($user->getEmailForVerification())
        ), Response::HTTP_FORBIDDEN);


        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        //return view('verified-account');
        return redirect()->away("http://" . urldecode($url) . "?email-verification-success&timeout=60");
    }

    function resendEmailVerification(Request $request)
    {
        $user = $request->user();
        $user->redirect_url = $request->url;
        $user->sendEmailVerificationNotification();

        return response()->json(
            ['success' => "a new email verification link has been sent"],
            Response::HTTP_OK
        );
    }
}
