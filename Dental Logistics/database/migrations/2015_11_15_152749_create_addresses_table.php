<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('company_id');
            $table->string('address_type');
            $table->string('name');
            $table->string('street1');
            $table->string('street2');
            $table->string('city');
            $table->string('county');
            $table->string('postcode');
            $table->string('country');
            $table->string('contact');
            $table->string('email');
            $table->string('phone');
            $table->string('residential',1);
            $table->timestamps();
            $table->index(array('customer_id','address_type'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('addresses');
    }

}
