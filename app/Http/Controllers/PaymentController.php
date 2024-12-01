<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\CoreApi;
use App\Models\Payment;
use Midtrans\Transaction;
use App\Models\ProcessImk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ResponseInterface;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        $payment = Payment::with('customer', 'user', 'process_imk')->find($id);

        $paidCount = Payment::where('status_pay', 'paid')->count();
        $pendingCount = Payment::where('status_pay', 'pending')->count();


        if ($payment) {
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'jumlah pembayaran selesai (paid)' => $paidCount,
                'jumlah pembayaran tertunda (pending)' => $pendingCount,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'payment not found',
            ], 404);
        }
        // return response()->json([
        //     'success' => true,
        //     'Payment' => Payment::all()->load('customer', 'user', 'process_imk'),
        // ]);
    }

    public function getAllData(Request $request)
{
    try {
        // Ambil user yang sedang login
        $user = Auth::user();
        
        // Jika user adalah admin, ambil semua data pembayaran
        if ($user->level === 'admin') {
            $payments = Payment::with('customer', 'user', 'process_imk')->get();
        } else {
            // Jika user adalah user biasa, ambil hanya data pembayaran milik user tersebut
            $payments = Payment::with('customer', 'user', 'process_imk')
                                ->where('user_id', $user->id) // Sesuaikan dengan kolom yang menghubungkan user dengan payment
                                ->get();
        }

        // Kembalikan data dalam format JSON
        return response()->json([
            'success' => true,
            'payment' => $payments,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve payments.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    // public function getAllData(Request $request)
    // {
    //     return response()->json([
    //         'success' => true,
    //         'payment' => Payment::with('customer', 'user', 'process_imk')->get(),
    //     ], 200);
    // }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'nullable',
            'id_customer' => 'nullable',
            // 'id_imk' => 'required|exists:process_imk,id',
            'name_pay' => 'required|string',
            'amount_pay' => 'required|numeric',
            'note_pay' => 'nullable|string',
            'pay_method' => 'required|string',
            'order_id' => 'nullable|string',
            'redirect_url' => 'nullable'
        ]);

        // $processImk = ProcessImk::find($id);
        $processImk = ProcessImk::find($request->id_imk);

        // dd($processImk);

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
            "payment_type" => "gopay",
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
                // 'midtrans_order_id' => $params['transaction_details']['order_id'],
                // 'midtrans_transaction_status' => "pending",
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


    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        // Config::$serverKey = config('midtrans.server_key');
        // Config::$isProduction = config('midtrans.is_production');
        // Config::$isSanitized = config('midtrans.is_sanitized');

        // try {
        //     $payment = Payment::find($id);
        //     Log::info($payment);
        //     // $paymentResponse = Snap::

        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Payment created. Please check payment status.',
        //         'payment_response' => $paymentResponse
        //     ], 201);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }
    }

    /**
     * Show the form for editing the specified resource.2
    /**
     * Update the specified resource in storage.
     */
    // public function update($id)
    // {
    //     $payment = Payment::find($id);

    //     if (!$payment) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Payment not found'
    //         ], 404);
    //     }

    //     // Update status pembayaran menjadi 'paid'
    //     $payment->update([
    //         'status_pay' => 'paid',
    //         'pay_date' => now()
    //     ]);

    //     // Update status IMK menjadi aktif (status_imk = true)
    //     $payment->imk()->update([
    //         'status_imk' => true
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Payment approved and IMK activated',
    //         'payment' => $payment
    //     ], 200);
    // }

    public function update($id)
{
    $payment = Payment::find($id);

    if (!$payment) {
        return response()->json([
            'success' => false,
            'message' => 'Payment not found'
        ], 404);
    }

    // Update status pembayaran menjadi 'paid'
    $payment->update([
        'status_pay' => 'paid',
        'pay_date' => now()
    ]);

    // Akses relasi process_imk yang berhubungan dengan payment
    $payment->process_imk()->update([
        'status_imk' => true
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Payment approved and IMK activated',
        'payment' => $payment
    ], 200);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function handleCallback(Request $request)
    {
        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));

        Log::info('incoming midtrans callback', $request->all());
        if ($signature != $request->signature_key) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 400);
        }

        $transaction = Payment::where('order_id', $request->order_id)->first();

        if ($transaction) {
            if ($request->transaction_status == 'settlement') {
                $transaction->update(['status_pay' => 'capture']);
            } elseif ($request->transaction_status == 'canceled') {
                $transaction->update(['status_pay' => 'canceled']);
            } elseif ($request->transaction_status == 'expired') {
                $transaction->update(['status_pay' => 'expired']);
            } else {
                $transaction->update(['status_pay' => 'pending']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback success'
        ]);
    }
}
