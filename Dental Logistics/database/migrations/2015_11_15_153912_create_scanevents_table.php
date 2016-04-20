<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScaneventTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('scanevents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_id')->index();
            $table->string('status');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->string('signed_by');
            $table->decimal('weight');
            $table->timestamp('est_delivery_date');
            $table->string('shipment_ident');
            $table->string('carrier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('scanevents');
    }

}
