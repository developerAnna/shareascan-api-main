<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Notifications\VerifyEmailCustom;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;


/**
 * @OA\Info(title="ShareaScan", version="0.1")
 */



class AuthController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     operationId="registerUser",
     *     tags={"User Authentication"},
     *     summary="Register a new user",
     *     description="Registers a new user and returns the user with an authentication token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "password", "confirm_password"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="your_generated_token_here"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="created_at", type="string", example="2025-03-06T11:44:17.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-03-06T11:44:17.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found"
     *     ),
     * )
     */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|unique:users,name',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email', // Unique validation for email
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $getAppName = env('APP_NAME');

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['name'] = $request->first_name;
        $user = User::create($input);

        $user->notify(new VerifyEmailCustom());

        $success['token'] = $user->createToken($getAppName)->plainTextToken;
        $success['user'] = new UserResource($user);

        return $this->sendResponse($success, 'User register successfully. Please check your email to verify.');
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     operationId="loginUser",
     *     tags={"User Authentication"},
     *     summary="Login a user",
     *     description="Log in a user and return an authentication token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="your-token-here"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorised.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Email not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Email not verified. Please verify before logging in.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found"
     *     ),
     * )
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $getAppName = env('APP_NAME');

        // Attempt to log the user in with email and password
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Check if the email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email not verified. Please verify your email before logging in.',
                ], 403);
            }

            // Create and return the API token
            $success['token'] = $user->createToken($getAppName)->plainTextToken;
            $success['user'] = $user;

            return $this->sendResponse($success, 'User logged in successfully.');
        }

        // If login attempt fails (either email or password is incorrect)
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid email or password.',
        ], 401);  // Unauthorized

    }


    /**
     * Handle forgot password request
     *
     * @OA\Post(
     *     path="/api/forgot-password",
     *     tags={"User Authentication"},
     *     summary="Send Password Reset Email",
     *     description="Send an email with the password reset link.",
     *     operationId="forgotPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password reset link sent successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="error"),
     *             @OA\Property(property="message", type="string", example="Email address not found.")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // $status = Password::sendResetLink(
        //     $request->only('email'),
        //     function ($user, $token) {
        //         Log::info($token);
        //         $resetUrl = env('FRONT_APP_URL') . '/reset-password/' . $token . '&email=' . urlencode($user->email);
        //         Log::info($resetUrl);
        //         $user->sendPasswordResetNotification($resetUrl);
        //     }
        // );

        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                $resetUrl = env('FRONT_APP_URL') . '/reset-password/' . $token . '?email=' . urlencode($user->email);

                $user->notify(new \App\Notifications\ResetPasswordNotification($resetUrl));
            }
        );

        // Return response based on status
        return $status === Password::RESET_LINK_SENT
            ? $this->sendResponse($request->email, 'Password reset link sent successfully.')
            : $this->sendError('Email address not found.');
    }


    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     operationId="resetPassword",
     *     tags={"User Authentication"},
     *     summary="Reset the password for a user",
     *     description="Reset the password for a user using a token, email, and the new password.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abc123"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password has been reset successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="generated-token-here"),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
     *                 @OA\Property(property="token", type="array", @OA\Items(type="string", example="The token field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid token or email.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid token or email.")
     *         )
     *     ),
     * )
     */
    public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->email)->first();
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['email'] =  $request->email;
            return $this->sendResponse($success, 'Password has been reset successfully.');
        } else {
            return $this->sendError('Invalid token or email.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     operationId="logoutUser",
     *     tags={"User Authentication"},
     *     summary="Logout the authenticated user",
     *     description="Logs out the authenticated user by revoking the access token.",
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorised.")
     *         )
     *     ),
     *      security={{"X-Access-Token": {}}}
     * )
     */

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/change-password",
     *     operationId="changePassword",
     *     tags={"User Authentication"},
     *     summary="Change the password for the authenticated user",
     *     security={{"X-Access-Token": {}}},
     *     description="Change the password for the authenticated user by providing the old password and a new password.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="old_password", type="string", format="password", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password changed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="old_password", type="array", @OA\Items(type="string", example="The old password field is required.")),
     *                 @OA\Property(property="new_password", type="array", @OA\Items(type="string", example="The new password field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Old password does not match.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Old password does not match.")
     *         )
     *     ),
     * )
     */

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = Auth::user();
        if (!Hash::check($request->old_password, $user->password)) {
            return $this->sendError('Old password does not match.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->sendResponse([], 'Password changed successfully.');
    }
}
