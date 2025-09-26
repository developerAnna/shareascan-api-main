<?php

namespace App\Http\Controllers\API;

use App\Mail\GeneralMail;
use App\Utilities\Overrider;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class ContactUsController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/contact-us",
     *     operationId="contactUs",
     *     tags={"Contact Us"},
     *     summary="Send a contact us message",
     *     description="Send a contact us message with the user's details and message.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "phone_number", "message"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="phone_number", type="string", example="+12345678901"),
     *             @OA\Property(property="message", type="string", example="I need assistance with my account."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your message has been sent successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="first_name", type="array", @OA\Items(type="string", example="The first name field is required.")),
     *                 @OA\Property(property="last_name", type="array", @OA\Items(type="string", example="The last name field is required.")),
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
     *                 @OA\Property(property="phone_number", type="array", @OA\Items(type="string", example="Please enter a valid phone number.")),
     *                 @OA\Property(property="message", type="array", @OA\Items(type="string", example="The message field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to submit contact us. Please try again.")
     *         )
     *     ),
     * )
     */

    public function contactUs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone_number' => [
                'required',
                'regex:/^\+?\d{10,11}$/',
            ],
            'message' => 'required',
        ], ['phone_number' => 'Please enter a valid phone number']);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }


        DB::beginTransaction();

        try {

            Overrider::load("Settings");

            //Replace paremeter
            $replace = array(
                '{name}'              => $request->first_name . ' ' . $request->last_name,
                '{email}'          => $request->email,
                '{phone_number}'      => $request->phone_number,
                '{message}'    => $request->message,
            );

            //Send contact email
            $template = EmailTemplate::where('slug', 'contact-us')->first();
            $template->body = process_string($replace, $template->body);

            $mail_send_to = get_options('get_contact_us_email_on');
            Mail::to($mail_send_to)->send(new GeneralMail($template));

            DB::commit();

            return $this->sendResponse([], 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending contact us: ' . $e->getMessage());
            return $this->sendError('error.', 'Failed to submit contact us. Please try again.');
        }
    }
}
