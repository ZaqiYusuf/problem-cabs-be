<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\ProcessImk;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request, $id)
    {
        $payment = Payment::with('customer', 'user', 'process_imk')->find($id);
        $paidCount = Payment::where('status_pay', 'paid')->count();
        $pendingCount = Payment::where('status_pay', 'pending')->count();

        if ($payment) {
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'jumlah_pembayaran_selesai' => $paidCount,
                'jumlah_pembayaran_tertunda' => $pendingCount,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }
    }

    public function getAllData(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->level === 'admin') {
                $payments = Payment::with('customer', 'user', 'process_imk')->get();
                $paidCount = Payment::where('status_pay', 'paid')->count();
                $pendingCount = Payment::where('status_pay', 'pending')->count();
            } else {
                $payments = Payment::with('customer', 'user', 'process_imk')->where('user_id', $user->id)->get();
                $paidCount = null;
                $pendingCount = null;
            }

            return response()->json([
                'success' => true,
                'payment' => $payments,
                'paid_payment' => $paidCount,
                'pending_payment' => $pendingCount,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'nullable',
            'id_customer' => 'required|exists:process_imk,customer_id',
            'id_imk' => 'required|exists:process_imk,id',
            'name_pay' => 'required|string',
            'amount_pay' => 'required|numeric',
            'note_pay' => 'nullable|string',
            'pay_method' => 'required|string',
        ]);

        $processImk = ProcessImk::find($request->id);
        if (!$processImk) {
            return response()->json([
                'success' => false,
                'message' => 'Process IMK not found'
            ]);
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');

        $order_id = 'IMK-' . uniqid();

        $params = [
            "payment_type" => $request->pay_method,
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $request->amount_pay,
            ],
            'customer_details' => [
                'first_name' => $request->name_pay,
                'email' => $request->user()->email,
            ],
            'item_details' => [
                [
                    'id' => $processImk->id,
                    'price' => $request->amount_pay,
                    'quantity' => 1,
                    'name' => 'Pembayaran IMK #' . $processImk->id,
                ]
            ]
        ];
        

        try {
            $paymentResponse = Snap::createTransaction($params);

            $payment = Payment::create([
                'user_id' => $request->user_id,
                'id_customer' => $processImk->customer_id,
                'id_imk' => $processImk->id,
                'pay_date' => now(),
                'amount_pay' => $request->amount_pay,
                'name_pay' => $request->name_pay,
                'note_pay' => $request->note_pay,
                'pay_method' => $request->pay_method,
                'status_pay' => 'pending',
                'redirect_url' => $paymentResponse->redirect_url,
                'order_id' => $order_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment created. Please check payment status.',
                'payment_response' => $paymentResponse
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));

        Log::info('Midtrans callback received', $request->all());

        if ($signature !== $request->signature_key) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 400);
        }

        $transaction = Payment::where('order_id', $orderId)->first();

        if ($transaction) {
            $status = $request->transaction_status;
            if ($status === 'settlement') {
                $transaction->update(['status_pay' => 'paid']);
            } elseif ($status === 'cancel' || $status === 'deny') {
                $transaction->update(['status_pay' => 'canceled']);
            } elseif ($status === 'expire') {
                $transaction->update(['status_pay' => 'expired']);
            } else {
                $transaction->update(['status_pay' => 'pending']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.'
            ], 404);
        }
    }
}
