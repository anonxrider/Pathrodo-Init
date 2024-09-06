<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Add this line to import the User model
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    //REGISTER ADMIN OR USER
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:user,admin'  // Ensure valid role
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create token for the registered user
        //$token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'message' => 'User registered sucessfully'
            //'token' => $token
        ], 201);
    }

    // Login Method
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token for the logged-in user
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
