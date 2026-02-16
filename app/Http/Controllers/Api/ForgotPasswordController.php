<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'We can\'t find a user with that e-mail address.'], 404);
        }

        // Generate 6-digit code
        $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Log the token to allow user to simulate "receiving email"
        \Log::info("Password Reset Code for {$request->email}: $token");

        try {
            Mail::to($request->email)->send(new \App\Mail\VerificationCodeMail($token));
        } catch (\Exception $e) {
            \Log::error("Mail sending failed: " . $e->getMessage());
            // We still return success so the user can use the token from logs if mail fails
        }

        return response()->json([
            'message' => 'Verification code sent!',
            'token_debug' => $token // Remove usage in production if not needed
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:6',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        if (!$resetRecord) {
             return response()->json(['message' => 'Invalid code.'], 400);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
             DB::table('password_reset_tokens')->where('email', $request->email)->delete();
             return response()->json(['message' => 'Code expired.'], 400);
        }

        return response()->json(['message' => 'Code verified.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        if (!$resetRecord) {
             return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        // Check if token is expired (e.g., 60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
             DB::table('password_reset_tokens')->where('email', $request->email)->delete();
             return response()->json(['message' => 'Code expired.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Your password has been reset!']);
    }
}
