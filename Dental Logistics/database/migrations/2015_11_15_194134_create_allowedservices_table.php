<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllowedserviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allowedservices', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('customer_id');
            $table->integer('carrier_id');
            $table->integer('service_id');
            $table->string('cost_account');
            $table->string('sales_account');
            $table->timestamps();
            $table->index(array('customer_id','carrier_id','service_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('allowedservices');
    }
}
