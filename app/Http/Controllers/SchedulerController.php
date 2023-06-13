<?php

namespace App\Http\Controllers;

use App\Model\Order;
use App\Model\OrderReturnReplace;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchedulerController extends Controller
{
    public function assignRiderForOrder()
    {
        $_ASSIGNBEFORE = 10; // Find Delivery Boy before 10 Minutes

        $orders = Order::whereIn('status', ['accepted', 'processed'])->where('deliveryboy_id', null)->get();
        foreach ($orders as $key => $order) {
            \Log::info('REGULER_ORDER : ' . $order->code . ' EXPECTED INTRANSIT TIME ' . Carbon::parse($order->expected_intransit)->format('Y-m-d H:i:s A'));

            if ($order->expected_intransit) {
                // $_SEARCHSTART = Carbon::parse($order->expected_intransit)->subMinutes($_ASSIGNBEFORE);
                // if (Carbon::now()->gte($_SEARCHSTART)) {
                if ($order->shop->shop_latitude && $order->shop->shop_longitude) {
                    \Log::info('REGULER_ORDER : ' . $order->code . ' SEARCH STARTED ' . Carbon::now()->format('Y-m-d H:i:s A'));
                    $avail_drivers = \Myhelper::getAvailableDrivers($order->shop->shop_latitude, $order->shop->shop_longitude, $order->id);
                    if (count($avail_drivers) > 0) {
                        $deliveryboy_id = $avail_drivers[0];
                        $action = Order::where('id', $order->id)->update(['deliveryboy_id' => $deliveryboy_id, 'deliveryboy_status' => 'pending']);
                        if ($action) {
                            $order = Order::with('deliveryboy')->where('id',  $order->id)->first();
                            if ($order) {
                                \Log::info('REGULER_ORDER : ' . $order->code . ' DELIVERY BOY ASSIGNED - ID : ' . $order->deliveryboy->id . ' - ' . Carbon::now()->format('Y-m-d H:i:s A'));

                                /**
                                 * ---------------------------------
                                 * MAKE ORDER ACTIONS
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusAction($order->id, 'deliveryassigned');
                            }
                        }
                    } else {
                        \Log::info('REGULER_ORDER : ' . $order->code . ' NO DELIVERY BOY FOUND AT ' . Carbon::now()->format('Y-m-d H:i:s A'));
                    }
                } else {
                    \Log::info('REGULER_ORDER : ' . $order->code . ' SHOP LATITUDE AND LONGITUDE NOT FOUND AT ' . Carbon::now()->format('Y-m-d H:i:s A'));
                }
                // } else {
                // \Log::info($order->code . ' SEARCH TIME TO BE START AFTER ' . $_SEARCHSTART->format('Y-m-d H:i:s A'));
                // }
            } else {
                \Log::info('REGULER_ORDER : ' . $order->code . ' NO EXPECTED INTRANSIT FOUND FOR ORDER');
            }
        }

        $returnreplace_orders = OrderReturnReplace::whereIn('status', ['accepted', 'processed'])->where('deliveryboy_id', null)->get();
        foreach ($returnreplace_orders as $key => $returnreplace_order) {
            \Log::info('RETURNREPLACE_ORDER : ' . $returnreplace_order->code . ' EXPECTING DELIVERY PARTNER');

            // if ($returnreplace_order->expected_intransit) {
            // $_SEARCHSTART = Carbon::parse($returnreplace_order->expected_intransit)->subMinutes($_ASSIGNBEFORE);
            // if (Carbon::now()->gte($_SEARCHSTART)) {
            // if ($returnreplace_order->order->shop->shop_latitude && $returnreplace_order->order->shop->shop_longitude) 
            if ($returnreplace_order->order && $returnreplace_order->order->shop && $returnreplace_order->order->shop->shop_latitude && $returnreplace_order->order->shop->shop_longitude) 
            {
                \Log::info('RETURNREPLACE_ORDER : ' . $returnreplace_order->code . ' SEARCH STARTED ' . Carbon::now()->format('Y-m-d H:i:s A'));
                $avail_drivers = \Myhelper::getAvailableDrivers($returnreplace_order->order->shop->shop_latitude, $returnreplace_order->order->shop->shop_longitude, $returnreplace_order->id, 10, false, 'returnreplace');
                
                if (count($avail_drivers) > 0) {
                    $deliveryboy_id = $avail_drivers[0];
                    $action = OrderReturnReplace::where('id', $returnreplace_order->id)->update(['deliveryboy_id' => $deliveryboy_id, 'deliveryboy_status' => 'pending']);
                    if ($action) {
                        $returnreplace_order = OrderReturnReplace::with('deliveryboy')->where('id',  $returnreplace_order->id)->first();
                        if ($returnreplace_order) {
                            \Log::info('RETURNREPLACE_ORDER : ' . $returnreplace_order->code . ' DELIVERY BOY ASSIGNED - ID : ' . $returnreplace_order->deliveryboy->id . ' - ' . Carbon::now()->format('Y-m-d H:i:s A'));

                            /**
                             * ---------------------------------
                             * MAKE ORDER ACTIONS
                             * ---------------------------------
                             */
                            \Myhelper::updateReturnReplaceStatusAction($returnreplace_order->id, 'deliveryassigned');
                        }
                    }
                } else {
                    \Log::info('RETURNREPLACE_ORDER : ' . $returnreplace_order->code . ' NO DELIVERY BOY FOUND AT ' . Carbon::now()->format('Y-m-d H:i:s A'));
                }

            } else {
                \Log::info('RETURNREPLACE_ORDER : ' . $returnreplace_order->code . ' SHOP LATITUDE AND LONGITUDE NOT FOUND AT ' . Carbon::now()->format('Y-m-d H:i:s A'));
            }
            // } else {
            // \Log::info($returnreplace_order->code . ' SEARCH TIME TO BE START AFTER ' . $_SEARCHSTART->format('Y-m-d H:i:s A'));
            // }
            // } else {
            //     \Log::info('RETURNREPLACE_ORDER : ' . $returnreplace_order->code . ' NO EXPECTED INTRANSIT FOUND FOR ORDER');
            // }
        }
    }

    public function estimateDeliveryTime()
    {
        $orders = Order::with('deliveryboy')->whereIn('status', ['outfordelivery'])->where('deliveryboy_id', '!=', null)->get();
        foreach ($orders as $key => $order) {
            $deliveryboy_lat = $order->deliveryboy->latitude;
            $deliveryboy_long = $order->deliveryboy->longitude;

            $cust_lat = $order->cust_latitude;
            $cust_long = $order->cust_longitude;

            $distance = \Myhelper::getDistanceMatric($deliveryboy_lat, $deliveryboy_long, $cust_lat, $cust_long);
            // if ($distance->duration_value > 60) {
            $expected_delivery = Carbon::now()->addSeconds($distance->duration_value);

            if ($order->expected_delivery) {
                $order_expected_delivery = Carbon::parse($order->expected_delivery);

                // Check if the expected delivery time is delayed
                if ($order_expected_delivery->diffInSeconds($expected_delivery) > 300) {
                    Order::where('id', $order->id)->update([
                        'expected_delivery' => $expected_delivery->addMinute(),
                        'delivery_status' => "delayed"
                    ]);

                    \Log::info($order->code . ' EXPECTED DELIVERY TIME HAS INCREASED, AND THE ORDER IS DELAYED');
                }
            } else {
                Order::where('id', $order->id)->update([
                    'expected_delivery' => $expected_delivery->addMinute()
                ]);
            }
            // }
        }
    }
}
