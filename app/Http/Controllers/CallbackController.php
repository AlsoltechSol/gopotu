<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function paytmorderpayment(Request $request)
    {
        \Log::info(Carbon::now()->format('Y-m-d H:i:s') . "PayTM Callback RCVD: " . json_encode($request->all()));

        // echo route('callback.paytmorderpayment');
        return response()->json($request->all());
    }
}
