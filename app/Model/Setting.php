<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name', 'title',
        'contactmobile', 'contactwhatsapp', 'contactemail', 'contactaddress',
        'deliverycharge_status', 'deliverycharge_perkm', 'deliverycharge_min', 'deliverycharge_freeordervalue', 'order_minval', 'order_storemindeliveryrange', 'order_storemaxdeliveryrange',
        'smsflag', 'smssender', 'smsuser', 'smspwd',
        'mailhost', 'mailport', 'mailenc', 'mailuser', 'mailpwd', 'mailfrom', 'mailname',
        'firstorder_userwallet_type', 'firstorder_userwallet_value', 'firstorder_parentwallet_type', 'firstorder_parentwallet_value',
        'maxwalletuse_mart', 'maxwalletuse_restaurant', 'maxwalletuse_service',
        'userapp_version', 'userapp_maintenancemsg', 'branchapp_version', 'branchapp_maintenancemsg', 'deliveryboyapp_version', 'deliveryboyapp_maintenancemsg','upto_3km', '_3km_to_5km', '_5km_to_8km'
    ];

    public $timestamps = false;
}
