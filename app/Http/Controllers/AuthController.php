<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Add this line to import the User model
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    // Forgot Password Request
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate a 6-digit OTP
        $otp = rand(10000000, 99999999);

        // Store OTP and expiration time
        $user->password_reset_otp = $otp;
        $user->password_reset_otp_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
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

    //REGISTER ADMIN OR USER
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Generate a 6-digit OTP
        $otp = rand(10000000, 99999999);

        // Store OTP and expiration time
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(10); // OTP expires in 10 minutes
        $user->save();

        // Send OTP via email
        Mail::raw("Your verification OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify Your Email Address');
        });

        return response()->json([
            'message' => 'User registered successfully. Please check your email for the OTP.'
        ], 201);
    }

    //Logout
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

    // Logout Method
    public function logout(Request $request)
    {
        // Revoke the user's token
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    //Get Profile
    public function getProfile(Request $request)
    {
        return response()->json([
            'user' => $request->user() // returns the authenticated user's details
        ], 200);
    }

    //Update Profile
    public function updateProfile(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $request->user()->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Update the user's information
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        // Save the updates
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

}
