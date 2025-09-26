<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LinkedSocialAccount;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Requests\SocialLoginRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;
use Laravel\Socialite\Two\User as ProviderUser;

class SocialLoginController extends BaseController
{
    // public function redirectToProvider($provider)
    // {
    //     $google_client_id = Setting::where('name', 'google_client_id')->value('value');
    //     $google_client_secret = Setting::where('name', 'google_client_secret')->value('value');
    //     $google_redirect_urls = Setting::where('name', 'google_redirect_url')->value('value');

    //     // dd($google_client_id, $google_client_secret, $google_redirect_urls);

    //     if (!$google_client_id || !$google_client_secret || !$google_redirect_urls) {
    //         return response()->json(['error' => 'Invalid provider or credentials not found.'], 404);
    //     }

    //     // Manually configure Socialite to use the credentials from the database
    //     config([
    //         'services.' . $provider => [
    //             'client_id' => $google_client_id,
    //             'client_secret' => $google_client_secret,
    //             'redirect' => $google_redirect_urls,
    //         ]
    //     ]);

    //     return response()->json(['url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()]);
    // }

    public function handleProviderCallback(Request $request)
    {
        $code = $request->get('code');

        if ($code) {
            // Exchange the authorization code for an access token
            $tokenResponse = Http::asForm()
                ->withOptions(['verify' => false])  // Disable SSL verification
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'redirect_uri' => 'http://127.0.0.1:8000/api/auth/google/callback',
                    'grant_type' => 'authorization_code',
                ]);

            // Extract the access token from the response
            $accessToken = $tokenResponse->json()['access_token'];

            // Debug the access token
            dd($accessToken);

            // Now you can use the access token to get the user's profile and email
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            $userInfo = $userResponse->json();
        }
        dd($request->all());
        try {

            $google_client_id = Setting::where('name', 'google_client_id')->value('value');
            $google_client_secret = Setting::where('name', 'google_client_secret')->value('value');
            $google_redirect_urls = Setting::where('name', 'google_redirect_url')->value('value');


            if (!$google_client_id || !$google_client_secret || !$google_redirect_urls) {
                return response()->json(['error' => 'Invalid provider or credentials not found.'], 404);
            }

            // Manually configure Socialite to use the credentials from the database
            config([
                'services.' . $provider => [
                    'client_id' => $google_client_id,
                    'client_secret' => $google_client_secret,
                    'redirect' => $google_redirect_urls,
                ]
            ]);
            // Get user details from provider
            $socialUser = Socialite::driver($provider)->stateless()->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))->user();

            // Find or create a user in the database
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider_id' => $socialUser->getId(),
                    'provider' => $provider,
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(16)), // Random password since social login is used
                ]);
            }

            $getAppName = env('APP_NAME');
            // Generate token for API
            $token = $user->createToken($getAppName)->plainTextToken;
            Log::info($token);

            // Return the token and user info
            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['error' => 'Unable to login, please try again.'], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/auth-login",
     *     operationId="socialLogin",
     *     tags={"socialLogin"},
     *     summary="Social login using Facebook, Google, or Apple",
     *     description="Allows users to log in using their social accounts (Facebook, Google, Apple) by providing an access token.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"access_token", "provider"},
     *             @OA\Property(property="access_token", type="string", example="your-access-token-here"),
     *             @OA\Property(property="provider", type="string", enum={"facebook", "google", "apple"}, example="facebook")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logged in successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged in successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="your-token-here")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Failed to login.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to login, try again.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object", example={"provider": {"The provider field is required."}, "access_token": {"The access token field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid provider or credentials not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid provider or credentials not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred on the server.")
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {

        if ($request->provider == 'facebook') {
            $client_id = Setting::where('name', 'facebook_client_id')->value('value');
            $client_secret = Setting::where('name', 'facebook_client_secret')->value('value');
            $redirect_urls = Setting::where('name', 'facebook_redirect_url')->value('value');
        } elseif ($request->provider == 'google') {
            $client_id = Setting::where('name', 'google_client_id')->value('value');
            $client_secret = Setting::where('name', 'google_client_secret')->value('value');
            $redirect_urls = Setting::where('name', 'google_redirect_url')->value('value');
        } elseif ($request->provider == 'apple') {
            $client_id = Setting::where('name', 'apple_client_id')->value('value');
            $client_secret = Setting::where('name', 'apple_client_secret')->value('value');
            $redirect_urls = Setting::where('name', 'apple_redirect_url')->value('value');
        }

        if (!$client_id || !$client_secret || !$redirect_urls) {
            return response()->json(['error' => 'Invalid provider or credentials not found.'], 404);
        }

        // Manually configure Socialite to use the credentials from the database
        config([
            'services.' . $request->provider => [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect' => $redirect_urls,
            ]
        ]);

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'provider' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }


        try {
            $accessToken = $request->get('access_token');
            $provider = $request->get('provider');
            // $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
            // $providerUser =  Socialite::driver($provider)->stateless()->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))->userFromToken($accessToken);;
            $providerUser = Socialite::driver($provider)
                ->stateless()
                ->scopes(['email'])  // Request the email permission
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->userFromToken($accessToken);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ]);
        }


        if (filled($providerUser)) {
            $user = $this->findOrCreate($providerUser, $provider);
        } else {
            $user = $providerUser;
        }

        auth()->login($user);
        if (auth()->check()) {
            $getAppName = env('APP_NAME');
            return response()->json([
                'message' => 'Logged in successfully',
                'data' => ['token' => auth()->user()->createToken($getAppName)->plainTextToken, 'user' => $user],
            ]);
        } else {
            return $this->error(
                message: 'Failed to Login try again',
                code: 401
            );
        }
    }


    protected function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        $linkedSocialAccount = LinkedSocialAccount::query()->where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->user;
        } else {
            $user = null;

            if ($email = $providerUser->getEmail()) {
                $user = User::query()->where('email', $email)->first();
            }

            if (! $user) {
                $user = User::query()->create([
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // Random password since social login is used
                ]);
                $user->markEmailAsVerified();
            }

            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;
        }
    }
}
