<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Add this line to import the User model
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Rules\ValidEmail;
use App\Rules\ValidName;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    //FORGET PASSWORD EMAIL SENT OTP
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        // Check if the OTP request count exceeds 3
        if ($user->otp_requests_count >= 3) {
            return response()->json([
                'message' => 'You have exceeded the maximum number of OTP requests. Please try again later.'
            ], 429);
        }
    
        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);
    
        // Store OTP and expiration time
        $user->password_reset_otp = $otp;
        $user->password_reset_otp_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
        $user->otp_requests_count += 1; // Increment OTP request count
        $user->save();
    
        // Send OTP via email
        Mail::raw("Your password reset OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset OTP');
        });
    
        return response()->json([
            'message' => 'OTP sent to your email. Please use it to reset your password.'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'otp' => 'required|integer',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check OTP validity
        if ($user->password_reset_otp !== $request->otp ||
            Carbon::now()->greaterThan($user->password_reset_otp_expires_at)) {
            return response()->json([
                'message' => 'Invalid or expired OTP.'
            ], 400);
        }

        // Update password
        $user->password = bcrypt($request->new_password);
        $user->password_reset_otp = null; // Clear OTP
        $user->password_reset_otp_expires_at = null; // Clear OTP expiration time
        $user->otp_requests_count = 0; // Reset OTP request count
        $user->save();

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ], 200);
    }

    //CHECK USER IS AUTHENTICATED OR NOT AUTHENTICATED
    public function getUser(Request $request)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            // Get the authenticated user
            $user = Auth::user();
            
            // Return only email and name as JSON
            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
    }

    
    //REGISTER ADMIN
    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[^\d\s][^\d]*$/', // Ensure name does not contain digits and does not start with a space
                new ValidName(), 
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                new ValidEmail(),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'role' => [
                'required',
                'string',
                'in:admin',
            ],
        ]);
        
        

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $user_name = $request->name;
        $uppercase_name = strtoupper($request->name);
        // Generate a 6-digit OTP
        $otp = rand(10000000, 99999999);

        // Store OTP and expiration time
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
        $user->save();

        // Send OTP via email
        Mail::raw("Hi $uppercase_name,\n\nYour verification OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify Your Email Address');
        });

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the OTP.'
        ], 201);
    }

    //REGISTER USER
    public function register(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[^\d\s][^\d]*$/', // Ensure name does not contain digits and does not start with a space
                new ValidName(), 
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                new ValidEmail(),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
            'role' => [
                'required',
                'string',
                'in:user',
            ],
        ]);
        
        

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $user_name = $request->name;
        $uppercase_name = strtoupper($request->name);
        // Generate a 6-digit OTP
        $otp = rand(10000000, 99999999);

        // Store OTP and expiration time
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
        $user->save();

        // Send OTP via email
        Mail::raw("Hi $uppercase_name,\n\nYour verification OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify Your Email Address');
        });

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the OTP.'
        ], 201);
    }

    //Login PUBLIC
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'status_message'=>'failed',
                'message' => 'User with the provided email does not exist.'
            ], 404);
        }

        // Check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status_message'=>'failed',
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if the email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'status_message'=>'failed',
                'message' => 'Please verify your email before logging in.'
            ], 403);
        }

        // Generate token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    //Admin Login
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'status_message' => 'failed',
                'message' => 'User with the provided email does not exist.'
            ], 404);
        }

        // Check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status_message' => 'failed',
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if the email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'status_message' => 'failed',
                'message' => 'Please verify your email before logging in.'
            ], 403);
        }

        // Check if the user is an admin
        if ($user->role !== 'admin') {
            return response()->json([
                'status_message' => 'failed',
                'message' => 'You are not an administrator.'
            ], 403); // Forbidden response for non-admin users
        }

        // Generate token for admin
        $token = $user->createToken('Admin Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Admin logged in successfully'
        ], 200);
    }

    // Logout Method
    public function logout(Request $request)
    {
        // Revoke the user's token
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // Get Profile
    public function getProfile(Request $request)
    {
        $user = $request->user();
        
        // Select only the required fields
        $profile = [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number ?? 'not available', // Replace null with 'not available'
            'profile_pic' => $user->profile_pic ?? 'not available', 
            'email_verified' => $user->email_verified_at ? true : false,
            'email_verified_time' => $user->email_verified_at
            //'role' => $user->role 
        ];

        return response()->json([
            'user' => $profile
        ], 200);
    }

    
    // Update Profile
    public function updateProfile(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
            'phone_number' => 'sometimes|nullable|string|regex:/^\+?[1-9]\d{1,14}$/', // Example: international phone number validation
            'profile_pic' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Example: image validation
        ]);
    
        // Get the authenticated user
        $user = $request->user();
    
        // Update the user's information
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        
        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
    
        if ($request->hasFile('profile_pic')) {
            // Handle file upload
            $file = $request->file('profile_pic');
            $path = $file->store('profile_pics', 'public'); // Store file in 'public/profile_pics' directory
            $user->profile_pic = $path;
        }
    
        // Save the updates
        $user->save();
    
        // Return only specific fields
        $profile = [
            'name' => $user->name,
            'email' => $user->email, // Email remains unchanged
            'phone_number' => $user->phone_number,
            'profile_pic' => $user->profile_pic ? Storage::url($user->profile_pic) : 'not available', // Provide URL if available
        ];
    
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $profile
        ], 200);
    }
   
    //CHANGE PASSWORD
    public function changePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 400); // HTTP status code 400 for bad request
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
    }

    //Deactivate Account
    public function deactivateAccount()
    {
        $user = Auth::user();

        // Deactivate the account (example: set a status flag)
        $user->is_active = false; // Add this column to your users table if needed
        $user->save();

        return response()->json([
            'message' => 'Account has been deactivated successfully.'
        ], 200);
    }

    //Delete Account
    public function deleteAccount()
    {
        $user = Auth::user();

        // Optionally, you might want to handle related data deletion here

        // Delete the user's account
        $user->delete();

        return response()->json([
            'message' => 'Account has been deleted successfully.'
        ], 200);
    }
}
