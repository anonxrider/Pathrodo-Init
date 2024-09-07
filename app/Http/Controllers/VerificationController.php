<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

            // Generate a token (assuming you're using Laravel Sanctum)
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Email verified successfully.',
                'token' => $token
            ], 200);
        }

        return response()->json(['message' => 'Invalid or expired OTP.'], 400);
    }

    // Resend OTP with throttling
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if the user has exceeded the allowed resend attempts
        if ($user->otp_resend_attempts >= 3) {
            // Check if 15 minutes have passed since the last resend
            $lastResend = Carbon::parse($user->last_otp_resend_at);
            if (Carbon::now()->diffInMinutes($lastResend) < 15) {
                $minutesLeft = 15 - Carbon::now()->diffInMinutes($lastResend);
                return response()->json([
                    'message' => "You've reached the resend limit. Please try again in $minutesLeft minutes."
                ], 429);
            } else {
                // Reset the attempts after 15 minutes
                $user->otp_resend_attempts = 0;
            }
        }

        // Generate a new OTP
        $otp = rand(10000000, 99999999);
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(10);

        // Update resend attempts and the last resend time
        $user->otp_resend_attempts += 1;
        $user->last_otp_resend_at = Carbon::now();
        $user->save();

        // Send OTP via email
        Mail::raw("Your new OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Resend OTP for Email Verification');
        });

        return response()->json([
            'message' => 'OTP resent successfully.',
            'attempts_left' => 3 - $user->otp_resend_attempts
        ], 200);
    }
}
