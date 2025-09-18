<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class UserController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/update-user-details/{id}",
     *     operationId="updateUserDetails",
     *     tags={"User Management"},
     *     summary="Update user details",
     *     description="Update the user's details.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the user"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "phone_number", "address_line_1", "city", "state", "country", "zipcode"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890"),
     *             @OA\Property(property="address_line_1", type="string", example="123 Main St"),
     *             @OA\Property(property="address_line_2", type="string", example="Apt 4B"),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="state", type="string", example="NY"),
     *             @OA\Property(property="country", type="string", example="USA"),
     *             @OA\Property(property="zipcode", type="string", example="10001")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", example="2025-03-06T11:44:49.000000Z"),
     *                 @OA\Property(property="created_at", type="string", example="2025-03-06T11:44:17.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-03-25T06:39:55.000000Z"),
     *                 @OA\Property(property="user_details", type="object",
     *                     @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                     @OA\Property(property="address_line_1", type="string", example="123 Main St"),
     *                     @OA\Property(property="address_line_2", type="string", example="Apt 4B"),
     *                     @OA\Property(property="city", type="string", example="New York"),
     *                     @OA\Property(property="state", type="string", example="NY"),
     *                     @OA\Property(property="country", type="string", example="USA"),
     *                     @OA\Property(property="zipcode", type="string", example="10001")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="User details updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="first_name", type="array", @OA\Items(type="string", example="The first name field is required.")),
     *                 @OA\Property(property="last_name", type="array", @OA\Items(type="string", example="The last name field is required.")),
     *                 @OA\Property(property="phone_number", type="array", @OA\Items(type="string", example="The phone number field is required.")),
     *                 @OA\Property(property="zipcode", type="array", @OA\Items(type="string", example="The zipcode must be 5 or 6 digits.")),
     *             )
     *         )
     *     ),
     *     security={{"X-Access-Token": {}}}
     * )
     */
    public function updateUserDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|unique:users,name,' . $id,
            'last_name' => 'required',
            'phone_number' => 'required|regex:/^\d{10,11}$/',
            'address_line_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'zipcode' => 'required|regex:/^\d{5,6}$/',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($id != Auth::user()->id) {
            return $this->sendError('Invalid User Id.', [], 422);
        }

        // Update the user's name
        $user = User::with('user_details')->where('id', Auth::user()->id)->first();
        if ($user) {
            $user->name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        } else {
            return $this->sendError('User not found.');
        }

        // Update or create user details
        $userDetails = UserDetails::where('user_id', Auth::user()->id)->first();
        if ($userDetails) {
            $userDetails->update($request->except('name'));
        } else {
            $inputs = $request->except('name');
            $inputs['user_id'] = Auth::user()->id;
            UserDetails::create($inputs);
        }

        return $this->sendResponse([
            'user' => new UserResource($user), // Use the resource to return formatted data
        ], 'User details updated successfully.');
    }


    /**
     * @OA\Get(
     *     path="/api/user-detail/{id}",
     *     operationId="getUserDetail",
     *     tags={"User Management"},
     *     summary="Fetch user details",
     *     description="Retrieve user details by user ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the user"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="email_verified_at", type="string", example="2025-03-06T11:44:49.000000Z"),
     *                 @OA\Property(property="created_at", type="string", example="2025-03-06T11:44:17.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-03-25T06:39:55.000000Z"),
     *                 @OA\Property(property="user_details", type="object",
     *                     @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                     @OA\Property(property="address_line_1", type="string", example="123 Main St"),
     *                     @OA\Property(property="address_line_2", type="string", example="Apt 4B"),
     *                     @OA\Property(property="city", type="string", example="New York"),
     *                     @OA\Property(property="state", type="string", example="NY"),
     *                     @OA\Property(property="country", type="string", example="USA"),
     *                     @OA\Property(property="zipcode", type="string", example="10001")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="User details fetched successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *      security={{"X-Access-Token": {}}}
     * )
     */
    public function getUserDetail(Request $request, $id)
    {

        try {
            if ($id != Auth::user()->id) {
                return $this->sendError('Invalid User ID.', [], 422);
            }

            $user = User::with('user_details')->where('id', Auth::user()->id)->first();

            if ($user) {
                return $this->sendResponse([
                    'user' => new UserResource($user),
                ], 'User details fetched successfully.');
            } else {
                return $this->sendError('User not found.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching user details.', $e->getMessage());
        }
    }
}
