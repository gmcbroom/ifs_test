<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->string('street1');
            $table->string('street2');
            $table->string('city');
            $table->string('county');
            $table->string('postcode');
            $table->string('country');
            $table->string('logo');
            $table->string('depot');
            $table->string('status');
            $table->string('account');
            $table->string('fuelcap')->default(99.99);
            $table->string('default_carrier');
            $table->string('salesman');
            $table->string('currency')->default('GBP');
            $table->string('weight_units');
            $table->string('dim_units');
            $table->boolean('carrier_pickup');
            $table->boolean('show_price');
            $table->boolean('vat_exempt');
            $table->boolean('driver_label');
            $table->boolean('customer_label');
            $table->boolean('summary_label');
            $table->string('label_format');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('companies');
    }

}
