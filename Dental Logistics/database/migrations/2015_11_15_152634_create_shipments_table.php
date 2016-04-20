<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipmentTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('master_scan')->index();
            $table->string('carrier_scan')->index();
            $table->string('carrier_id');
            $table->string('status');
            $table->bigInteger('company_id');
            $table->bigInteger('customer_id');
            $table->string('shipper_name');
            $table->string('shipper_street1');
            $table->string('shipper_street2');
            $table->string('shipper_city');
            $table->string('shipper_county');
            $table->string('shipper_postcode');
            $table->string('shipper_country');
            $table->string('shipper_contact');
            $table->string('shipper_email');
            $table->string('shipper_phone');
            $table->string('consignee_name');
            $table->string('consignee_street1');
            $table->string('consignee_street2');
            $table->string('consignee_city');
            $table->string('consignee_county');
            $table->string('consignee_postcode');
            $table->string('consignee_country');
            $table->string('consignee_contact');
            $table->string('consignee_email');
            $table->string('consignee_phone');
            $table->string('customer_reference');
            $table->integer('pack_count');
            $table->decimal('total_weight');
            $table->decimal('total_volume');
            $table->string('weight_units');
            $table->string('service_code');
            $table->string('carrier_service');
            $table->boolean('options');
            $table->string('bill_account');
            $table->string('bill_postcode');
            $table->string('bill_country');
            $table->integer('quote_id');
            $table->string('payment_terms');
            $table->string('payor_account');
            $table->boolean('guaranteed');
            $table->bigInteger('customs_info');
            $table->bigInteger('created_by');
            $table->date('date_available');
            $table->time('time_available');
            $table->date('delivery_date');
            $table->boolean('collected');
            $table->boolean('uploaded');
            $table->date('uploaded_date');
            $table->time('uploaded_time');
            $table->string('uploaded_ref');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('shipments');
    }

}
