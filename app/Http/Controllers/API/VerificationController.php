<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


class VerificationController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/email/resend-verification",
     *     operationId="resendVerificationEmail",
     *     tags={"User Authentication"},
     *     summary="Resend the email verification link",
     *     description="Resends the verification email to the user if the email is not verified.",
     *     @OA\Response(
     *         response=200,
     *         description="Verification email resent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification email resent successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not authenticated.")
     *         )
     *     ),
     *      security={{"X-Access-Token": {}}}
     * )
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email resent successfully.'
        ], 200);
    }


    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.'
            ], 200);
        }

        // Verify the hash using Laravel's default hash comparison for email verification
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.'
            ], 400);
        }

        // Mark the user's email as verified
        $user->markEmailAsVerified();

        // Trigger the verified event
        event(new Verified($user));

        Auth::login($user);

        $baseUrl = env('FRONT_APP_URL');
        $redirectUrl = $baseUrl . '/login';
        // return redirect('https://shareascan.com/user/login')->with('message', 'Email verified successfully.');
        return redirect($redirectUrl)->with('message', 'Email verified successfully.');
    }
}
