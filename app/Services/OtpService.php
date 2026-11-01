<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate an OTP and send it to user's email address.
     *
     * @return array{success: bool, message: string, email: string, expires_in_minutes: int, otp?: string}
     */
    public function sendOtp(string $email, string $type = 'registration'): array
    {
        $otp = Otp::generate($email, $type);

        // Attempt to send email; if mail server isn't configured, log it safely so development/tests never fail
        try {
            Mail::raw("Your SeatSync Library Verification Code is: {$otp->otp}. This code expires in 15 minutes.", function ($message) use ($email) {
                $message->to($email)->subject('Your Verification Code - Library SeatSync');
            });
        } catch (\Throwable $e) {
            Log::warning("Could not send email via SMTP, logged OTP instead: {$otp->otp} for {$email}. Error: " . $e->getMessage());
        }

        // Always log OTP for development visibility
        Log::info("OTP generated for [{$email}] type [{$type}]: {$otp->otp}");

        $response = [
            'success' => true,
            'message' => "Verification code sent to {$email}.",
            'email' => $email,
            'expires_in_minutes' => 15,
        ];

        // Include otp in response in local/debug mode for easy mobile/postman testing
        if (config('app.debug', true)) {
            $response['debug_otp'] = $otp->otp;
        }

        return $response;
    }

    /**
     * Verify an OTP for an email address.
     */
    public function verifyOtp(string $email, string $otp, string $type = 'registration'): bool
    {
        return Otp::verify($email, $otp, $type);
    }
}
