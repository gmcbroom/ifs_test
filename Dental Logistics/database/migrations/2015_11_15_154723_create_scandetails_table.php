<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScandetailTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('scandetails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_id')->index();
            $table->string('message');
            $table->string('status');
            $table->timestamp('datetime');
            $table->string('town');
            $table->string('county');
            $table->string('country');
            $table->string('postcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('scandetails');
    }

}
