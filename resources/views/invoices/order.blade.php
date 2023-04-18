@section('pageheader', 'Order : ' . $order->code)
@extends('layouts.invoice')

@section('content')
    <section class="invoice">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="page-header" style="width: 97%">
                    <b class="text-uppercase">{{ config('app.name', 'Laravel') }}</b>
                    <small class="pull-right">Date: <b>{{ $order->created_at }}</b></small>
                </h2>
            </div>
        </div>

        <div class="row invoice-info">
            <div class="col-xs-12 table-responsive" style="border: 0">
                <table style="width: 100%; border: 0">
                    <tr style="border: 0">
                        <td style="">
                            <div class="invoice-col">
                                From
                                <address>
                                    <strong>{{ $order->shop->shop_name }}</strong><br>
                                    {{ $order->shop->shop_address->address_line1 ? $order->shop->shop_address->address_line1.',' : '' }}
                                    {{ $order->shop->shop_address->address_line2 ? $order->shop->shop_address->address_line2.',' : '' }}
                                    {{ $order->shop->shop_address->postal_code ? $order->shop->shop_address->postal_code.',' : '' }}
                                    {{ $order->shop->shop_address->city ? $order->shop->shop_address->city.',' : '' }}
                                    {{ $order->shop->shop_address->state ? $order->shop->shop_address->state : '' }}<br>
                                    Phone: {{ $order->shop->shop_mobile }}<br>
                                    Email: {{ $order->shop->shop_email }}
                                </address>
                            </div>
                        </td>
                        <td style="">
                            <div class="invoice-col">
                                To
                                <address>
                                    <strong>{{ $order->cust_name }}</strong><br>
                                    {{ $order->cust_address->address_line1 ? $order->cust_address->address_line1.',' : '' }}
                                    {{ $order->cust_address->address_line2 ? $order->cust_address->address_line2.',' : '' }}
                                    {{ $order->cust_address->postal_code ? $order->cust_address->postal_code.',' : '' }}
                                    {{ $order->cust_address->city ? $order->cust_address->city.',' : '' }}
                                    {{ $order->cust_address->state ? $order->cust_address->state : '' }}<br>
                                    Phone: {{ $order->cust_mobile }}<br>
                                    Email: {{ $order->user->email }}
                                </address>
                            </div>
                        </td>
                        <td>
                            <div class="invoice-col">
                                Order Code: <b> #{{ $order->code }}</b><br>
                                Payment Mode: <b class="text-uppercase">{{ $order->payment_mode }}</b><br>

                                Order Status:
                                <span class="text-uppercase">
                                    <b>{{ @config('orderstatus.options')[$order->status] ?? $order->status }}</b>
                                </span>
                            </span>
                            <br>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 table-responsive" style="padding: 0px !important;">
            <table class="table table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Product</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Gross Amount</th>
                        <th class="text-right">Taxable Amount</th>
                        <th class="text-right">CGST</th>
                        <th class="text-right">SGST</th>
                        <th class="text-right">IGST</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->order_products as $item)
                        <tr>
                            <td>{{$item->quantity}}</td>
                            <td>
                                {{$item->product->details->name}}
                                @if($item->variant_selected->color_name)
                                    &nbsp;| Color: {{$item->variant_selected->color_name}}
                                @endif

                                @if($item->variant_selected->variant_name)
                                    &nbsp;| {{$item->variant_selected->variant_name}}
                                @endif
                            </td>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)$item->price, 2, '.', '') }}</td>

                            @php
                                $grossamount = round($item->sub_total, 2);
                                $tax_total = round($item->tax_total, 2);

                                $shop_tin = $item->shop_tin;

                                $cust_state = DB::table('state_masters')->where('state_code', @$order->cust_address->state)->first();
                                $cust_tin = @$cust_state->tin_no ?? null;

                                $taxableamount = 0;
                                $cgst = 0;
                                $sgst = 0;
                                $igst = 0;
                                $totalaftertax = 0;

                                if ($shop_tin && $cust_tin) {
                                    $taxableamount = round($grossamount - $tax_total, 2);
                                    if ($shop_tin == $cust_tin) {
                                        $cgst = round($tax_total / 2, 2);
                                        $sgst = round($tax_total / 2, 2);
                                    } else{
                                        $igst = round($tax_total, 2);
                                    }
                                }

                                $totalaftertax = ($taxableamount + $cgst + $sgst + $igst);
                            @endphp

                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)($grossamount), 2, '.', '') }}</td>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)($taxableamount), 2, '.', '') }}</td>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)($cgst), 2, '.', '') }}</td>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)($sgst), 2, '.', '') }}</td>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)($igst), 2, '.', '') }}</td>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float)($totalaftertax), 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 table-responsive" style="padding: 0px !important">
            <table class="table" style="width: 100%">
                <tbody>
                    <tr>
                        <th style="width:50%">Subtotal</th>
                        <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float) $order->item_total, 2, '.', '') }}</td>
                    </tr>

                    <tr>
                        <th style="width:50%">Delivery Charge</th>
                        <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float) $order->delivery_charge, 2, '.', '') }}</td>
                    </tr>

                    @if ($order->coupon_discount > 0)
                        <tr>
                            <th>Coupon Discount</th>
                            <td class="text-right">{!! config('app.currency.code') !!} {{ number_format((float) $order->coupon_discount, 2, '.', '') }} (-)</td>
                        </tr>
                    @endif

                    <tr class="bg-teal">
                        <th>Payable Amount</th>
                        <td class="text-right">
                            <h4 class="">{!! config('app.currency.code') !!} {{ number_format((float) $order->payable_amount, 2, '.', '') }} (Inc. Tax)</h4>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($order->return_replacements as $returnreplacement)
        <div class="row">
            <div class="col-md-12" style="padding: 0px !important;">
                <div class="box">
                    <div class="box-header bg-info">
                        <h3 class="box-title text-uppercase">{{$returnreplacement->type}} Request</h3>

                        <div class="box-tools pull-right">
                            <b>{{$returnreplacement->code}}</b>
                        </div>
                    </div>
                    <div class="box-body" style="border: 1px solid #eee">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Product</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($returnreplacement->returnreplacement_items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $item->orderproduct->product->details->name }}

                                                    @if($item->orderproduct->variant_selected->color_name)
                                                        &nbsp;| Color: {{$item->orderproduct->variant_selected->color_name}}
                                                    @endif

                                                    @if($item->orderproduct->variant_selected->variant_name)
                                                        &nbsp;| {{$item->orderproduct->variant_selected->variant_name}}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tr class="">
                                        <td>Current Status</td>
                                        <td class="">
                                            <b>{{ @config('returnreplacestatus.options')[$returnreplacement->status] ?? $returnreplacement->status }}</b>
                                        </td>
                                    </tr>
                                    <tr class="no-print">
                                        <td>Initiation Date</td>
                                        <td class="">
                                            <b>{{ $returnreplacement->created_at }}</b>
                                        </td>
                                    </tr>
                                    <tr class="no-print">
                                        <td>Last Updated</td>
                                        <td class="">
                                            <b>{{ $returnreplacement->updated_at }}</b>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="row">
        <div class="col-md-12">
            <p class="text-center"><i>This is a computer generated invoice. No signature required.</i></p>
        </div>
    </div>
</section>
@endsection
