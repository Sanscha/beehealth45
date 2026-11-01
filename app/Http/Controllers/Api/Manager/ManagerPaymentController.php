<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagerPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Get library registration fee status & live QR/Razorpay checkout details.
     */
    public function getPaymentDetails(Request $request): JsonResponse
    {
        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        $paymentRef = 'LIBREG-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        $upiDetails = $this->paymentService->generateUpiQr(
            (float) $library->registration_fee,
            $paymentRef,
            "Reg fee for {$library->name}"
        );
        $razorpayOrder = $this->paymentService->createRazorpayOrder(
            (float) $library->registration_fee,
            $paymentRef
        );

        return response()->json([
            'success' => true,
            'library' => [
                'id' => $library->id,
                'name' => $library->name,
                'payment_status' => $library->payment_status,
                'status' => $library->status,
                'registration_fee' => (float) $library->registration_fee,
            ],
            'payment' => [
                'amount' => (float) $library->registration_fee,
                'currency' => 'INR',
                'ref_id' => $paymentRef,
                'upi' => $upiDetails,
                'razorpay' => $razorpayOrder,
            ],
        ], 200);
    }

    /**
     * Verify and record library registration fee payment (UPI QR or Razorpay).
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'payment_method' => 'required|in:upi,online_razorpay,cash',
            'transaction_id' => 'nullable|string',
            'razorpay_payment_id' => 'nullable|string',
            'razorpay_order_id' => 'nullable|string',
            'razorpay_signature' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $library = $user->library;

        if (!$library) {
            return response()->json([
                'success' => false,
                'message' => 'No library linked to this manager account.',
            ], 404);
        }

        if ($library->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Library registration fee has already been paid and activated.',
                'library' => $library,
            ], 200);
        }

        $txnId = null;

        if ($fields['payment_method'] === 'online_razorpay') {
            $paymentId = $fields['razorpay_payment_id'] ?? null;
            $orderId = $fields['razorpay_order_id'] ?? null;
            $sig = $fields['razorpay_signature'] ?? null;

            if (!$paymentId || !$orderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'razorpay_payment_id and razorpay_order_id are required for online Razorpay verification.',
                ], 422);
            }

            $isValid = $this->paymentService->verifyRazorpaySignature($orderId, $paymentId, (string) $sig);
            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Razorpay payment signature verification failed.',
                ], 422);
            }

            $txnId = $paymentId;
        } else {
            // UPI QR payment (e.g. UTR reference number or auto-generated reference)
            $txnId = !empty($fields['transaction_id'])
                ? $fields['transaction_id']
                : Payment::generateTransactionId('UPI-REG');
        }

        // Create Payment transaction record
        $payment = Payment::create([
            'user_id' => $user->id,
            'library_id' => $library->id,
            'booking_id' => null,
            'plan_id' => null,
            'collected_by' => null,
            'amount' => $library->registration_fee,
            'type' => 'library_registration',
            'currency' => 'INR',
            'payment_method' => $fields['payment_method'],
            'transaction_id' => $txnId,
            'status' => 'completed',
            'notes' => $fields['notes'] ?? "Registration and onboarding fee paid for {$library->name}",
        ]);

        // Activate library
        $library->payment_status = 'paid';
        $library->status = 'active';
        $library->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully! Your library is now active on SeatSync.',
            'library' => $library,
            'payment' => $payment,
        ], 200);
    }
}
