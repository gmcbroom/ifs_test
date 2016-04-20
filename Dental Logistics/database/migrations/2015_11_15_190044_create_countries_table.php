<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('alpha2')->index();
            $table->string('name');
            $table->string('alpha3')->index();
            $table->string('iso')->index();
            $table->boolean('eu');
            $table->string('postcode_validation');
            $table->string('postcode_example');
            $table->boolean('display');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('countries');
    }

}
