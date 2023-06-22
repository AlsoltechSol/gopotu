<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $settings = \App\Model\Setting::findorfail(1);

        \Config::set([
            'app.name' => $settings->name,
            'app.title' => $settings->title,

            'sms.flag' => $settings->smsflag,
            'sms.sender' => $settings->smssender,
            'sms.user' => $settings->smsuser,
            'sms.pwd' => $settings->smspwd,

            'mail.host' => $settings->mailhost,
            'mail.port' => $settings->mailport,
            'mail.encryption' => $settings->mailenc,
            'mail.username' => $settings->mailuser,
            'mail.password' => $settings->mailpwd,
            'mail.from.address' => $settings->mailfrom,
            'mail.from.name' => $settings->mailname,

            'app.deliverycharge_status' => $settings->deliverycharge_status,
            'app.deliverycharge_perkm' => $settings->deliverycharge_perkm,
            'app.deliverycharge_min' => $settings->deliverycharge_min,
            'app.upto_3km' => $settings->upto_3km,
            'app._3km_to_5km' => $settings->_3km_to_5km,
            'app._5km_to_8km' => $settings->_5km_to_8km,
            'app.deliverycharge_min' => $settings->deliverycharge_min,
            'app.deliverycharge_freeordervalue' => $settings->deliverycharge_freeordervalue,
            'app.order_minval' => $settings->order_minval,
            'app.order_storemindeliveryrange' => $settings->order_storemindeliveryrange,
            'app.order_storemaxdeliveryrange' => $settings->order_storemaxdeliveryrange,

            'app.taxamount' => 0,
            'app.shortname' => "GP",

            'contact.mobile' => $settings->contactmobile,
            'contact.whatsapp' => $settings->contactwhatsapp,
            'contact.email' => $settings->contactemail,
            'contact.address' => $settings->contactaddress,

            'google.apikey' => "AIzaSyCLBdfxCZAHz73ewTMiueV0V59wT6nxv38",

            'onesignal' => [
                'appid' => "90ee849d-424e-49bd-b7ae-1d2f10a96d0e",
                'apikey' => "YmE1YzVjMjItNWViMi00ZjdjLWJjNjItNDk4ZjU4NjI0ZTAx",
            ],

            'paytm.MERCHANT_ID' => "EwTBau83431325967487",
            'paytm.MERCHANT_KEY' => "O917&T9a88lJ&D@p",
            'paytm.WEBSITE_NAME' => "WEBSTAGING",
            'paytm.INDUSTRY_TYPE' => "Retail",

            'default.shopid' => "1",

            'orderstatus.options' => [
                'paymentinitiated' => 'Payment Initiated',
                'paymentfailed' => 'Payment Failed',
                'received' => 'Order Placed',
                'accepted' => 'Order Accepted',
                'processed' => 'Order Processing',
                'intransit' => 'Ready for Pickup',
                'outfordelivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
                'returned' => 'Returned',
            ],

            'returnreplacestatus.options' => [
                'initiated' => 'Initiated',
                'accepted' => 'Accepted',
                'processed' => 'Processing',
                'intransit' => 'Ready for Pickup',
                'outfordelivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'outforpickup' => 'Out for Pickup',
                'outforstore' => 'Out for Store',
                'deliveredtostore' => 'Delivered to Store',
                'rejected' => 'Rejected',
            ],

            'fundbank.accno' => '1234567890',
            'fundbank.ifsccode' => 'TEST001',
            'fundbank.accholder' => 'GoPotu LTD',
            'fundbank.bankname' => 'State Bank of India',

            'firstorder.userwallet.type' => $settings->firstorder_userwallet_type,
            'firstorder.userwallet.value' => $settings->firstorder_userwallet_value,
            'firstorder.parentwallet.type' => $settings->firstorder_parentwallet_type,
            'firstorder.parentwallet.value' => $settings->firstorder_parentwallet_value,

            'maxwalletuse' => [
                'mart' => $settings->maxwalletuse_mart,
                'restaurant' => $settings->maxwalletuse_restaurant,
                'service' => $settings->maxwalletuse_service,
            ],

            'firebasepush' => [
                'userapp' => [
                    'serverkey' => "AAAATU8_jt8:APA91bGaWpbrcbPzobzNlFq2xyq4_ESaMtt-nr1qPZcV93KlL7VjEZcuAyj5DcdbfD9IB4DKOJC7vStnJhaRZSOiu_Sh2C9JwxmtEHk4TyrIk2eFtPoCxDfwq9hQzGIqBbXujYS7juyo",
                    'senderid' => "332042047199"
                ],
                'branchapp' => [
                    'serverkey' => "AAAAanPa9Co:APA91bHYOebXIDwvHCtolcR6dC3tRXHIQ0-tnnvZwZuFEVbrm0lkMUW9kht9uKkTkAFsvX46Mz8s6emgIJwuEg2hqn1ZMJZ4DDoIDLSlMy1gY_MmoiI08N_KBFeoh1WIqr-cO2I7zlCg",
                    'senderid' => "457210262570"
                ],
                'deliveryboyapp' => [
                    'serverkey' => "AAAAamybiis:APA91bGgdqwUXQtThZwwze8CW1m2L6hZBBlqHXQ6STPn98yDVCk8TfWZv1VcAwjFqKftRDYGt61hzKDLea1SwC0XXzetwcxaCjGuxrcTgBV1pKW_8iSO0De_hW46t6BXIBNC48Qgu3A4",
                    'senderid' => "457088666155"
                ],
            ],

            'appsettings' => [
                'userapp' => [
                    'version' => $settings->userapp_version,
                    'maintenance_message' => $settings->userapp_maintenancemsg ?? null,
                ],
                'branchapp' => [
                    'version' => $settings->branchapp_version,
                    'maintenance_message' => $settings->branchapp_maintenancemsg ?? null,
                ],
                'deliveryboyapp' => [
                    'version' => $settings->deliveryboyapp_version,
                    'maintenance_message' => $settings->deliveryboyapp_maintenancemsg ?? null,
                ],
            ],
        ]);
    }
}
