<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('rates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shipment_id')->index();
            $table->string('service');
            $table->string('carrier');
            $table->string('currency');
            $table->decimal('rate');
            $table->integer('delivery_days');
            $table->date('delivery_date');
            $table->boolean('delivery_date_guaranteed');
            $table->boolean('accepted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('rates');
    }

}
