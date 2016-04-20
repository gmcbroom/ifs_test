<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackagetypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packagetypes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('carrier_id')->index();
            $table->string('package_type');
            $table->string('package_code');
            $table->timestamps();
            $table->index(array('carrier_id', 'package_type'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('packagetypes');
    }
}
