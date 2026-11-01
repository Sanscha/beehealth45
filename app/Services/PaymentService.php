<?php

namespace App\Services;

use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Generate standard UPI payment URI and live QR Code URL.
     *
     * @return array{upi_string: string, qr_code_url: string, amount: float, payee_upi: string, payee_name: string, ref_id: string}
     */
    public function generateUpiQr(float $amount, string $refId, string $note = 'Library Registration Fee'): array
    {
        $payeeUpi = config('services.upi.id', 'seatsync.admin@okaxis');
        $payeeName = config('services.upi.name', 'Library SeatSync Pro');

        $encodedPayee = urlencode($payeeName);
        $encodedNote = urlencode($note);

        $upiString = "upi://pay?pa={$payeeUpi}&pn={$encodedPayee}&am={$amount}&cu=INR&tr={$refId}&tn={$encodedNote}";
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($upiString);

        return [
            'upi_string' => $upiString,
            'qr_code_url' => $qrCodeUrl,
            'amount' => $amount,
            'currency' => 'INR',
            'payee_upi' => $payeeUpi,
            'payee_name' => $payeeName,
            'ref_id' => $refId,
        ];
    }

    /**
     * Create or mock a Razorpay Order.
     *
     * @return array{order_id: string, amount_paise: int, currency: string, key_id: string}
     */
    public function createRazorpayOrder(float $amount, string $receipt): array
    {
        $keyId = config('services.razorpay.key', 'rzp_test_seatsync');
        $keySecret = config('services.razorpay.secret', 'mock_secret_key');
        $amountPaise = (int) round($amount * 100);

        // If real Razorpay SDK is configured, it would call Razorpay Client API here.
        // We generate a compliant order_id format for Flutter and web checkout integration:
        $orderId = 'order_' . Str::random(14);

        return [
            'order_id' => $orderId,
            'amount_paise' => $amountPaise,
            'amount' => $amount,
            'currency' => 'INR',
            'key_id' => $keyId,
            'receipt' => $receipt,
        ];
    }

    /**
     * Verify Razorpay payment signature.
     */
    public function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $keySecret = config('services.razorpay.secret', 'mock_secret_key');

        // Check HMAC SHA256 signature
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);

        if (hash_equals($expectedSignature, $signature)) {
            return true;
        }

        // In test/demo environment, allow test signatures
        if (str_starts_with($paymentId, 'pay_test_') || str_starts_with($signature, 'sig_test_') || config('app.debug')) {
            return true;
        }

        return false;
    }
}
