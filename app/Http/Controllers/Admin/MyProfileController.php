<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MyProfileController extends Controller
{
    public function editProfile(Request $request)
    {
        $user = Auth::user();
        return view('admin.myprofile.form', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Define the validation rules
        $validator = Validator::make($request->all(), [
            // Validation for the name and email
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,

            // Validation for password change
            'password' => ['required_with:new_password', 'min:8', new MatchOldPassword],
            'new_password' => 'required_with:password|min:8|same:confirm_password',
            'confirm_password' => 'required_with:new_password|min:8',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return redirect()->route('admin.edit.profile')
                ->withErrors($validator)
                ->withInput();
        }

        // Update the basic info (name, email)
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Check and update password if fields are provided
        if ($request->has('password') && $request->has('new_password')) {
            // Ensure the current password matches
            if (!Hash::check($request->password, $user->password)) {
                return redirect()->back()->withErrors(['password' => 'Current password is incorrect.']);
            }

            // Update the password
            $user->password = Hash::make($request->new_password);
        }

        // Save the user
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
