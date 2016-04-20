<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model {

    protected $fillable = [
        'master_scan',
        'carrier_scan',
        'carrier_id',
        'status',
        'company_id',
        'customer_id',
        'shipper_name',
        'shipper_street1',
        'shipper_street2',
        'shipper_city',
        'shipper_county',
        'shipper_postcode',
        'shipper_country',
        'shipper_contact',
        'shipper_email',
        'shipper_phone',
        'consignee_name',
        'consignee_street1',
        'consignee_street2',
        'consignee_city',
        'consignee_county',
        'consignee_postcode',
        'consignee_country',
        'consignee_contact',
        'consignee_email',
        'consignee_phone',
        'customer_reference',
        'pack_count',
        'total_weight',
        'total_volume',
        'weight_units',
        'service_code',
        'carrier_service',
        'options',
        'bill_account',
        'bill_postcode',
        'bill_country',
        'quote_id',
        'payment_terms',
        'payor_account',
        'guaranteed',
        'customs_info',
        'created_by',
        'date_available',
        'time_available',
        'delivery_date',
        'collected',
        'uploaded',
        'uploaded_date',
        'uploaded_time',
        'uploaded_ref',
    ];

    /**
     * Get Labels
     */
    public function label() {
        return $this->hasMany('App\Label');
    }

    public function order() {
        return $this->hasOne('App\Order');
    }

}
