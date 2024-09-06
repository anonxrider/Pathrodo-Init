<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

class VerificationController extends Controller
{
    // Verify the OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|integer',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if the OTP is valid and not expired
        if ($user->email_otp === $request->otp && Carbon::now()->lessThanOrEqualTo($user->email_otp_expires_at)) {
            // Mark email as verified and clear OTP
            $user->email_verified_at = Carbon::now();
            $user->email_otp = null;
            $user->email_otp_expires_at = null;
            $user->save();

            return response()->json(['message' => 'Email verified successfully.'], 200);
        }

        return response()->json(['message' => 'Invalid or expired OTP.'], 400);
    }
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate a new OTP
        $otp = rand(100000, 999999);
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Send OTP via email
        Mail::raw("Your new OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Resend OTP for Email Verification');
        });

        return response()->json(['message' => 'OTP resent successfully.'], 200);
    }
}
