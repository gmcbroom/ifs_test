<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('shipment_id')->index();
            $table->decimal('weight');
            $table->decimal('width');
            $table->decimal('height');
            $table->string('dim_units');
            $table->string('package_type');
            $table->string('package_scan');
            $table->decimal('dryice_weight');
            $table->decimal('dryice_units');
            $table->boolean('bio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('packages');
    }
}
