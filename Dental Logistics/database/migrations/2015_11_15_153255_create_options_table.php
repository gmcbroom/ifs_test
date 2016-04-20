<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shipment_id')->index();
            $table->string('alcohol');
            $table->string('dangerous_goods');
            $table->string('dry_ice');
            $table->string('dry_ice_medical_use');
            $table->string('dry_ice_weight');
            $table->string('handling_instructions');
            $table->string('hold_for_pickup');
            $table->string('saturday_delivery');
            $table->string('delivery_confirmation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('options');
    }

}
