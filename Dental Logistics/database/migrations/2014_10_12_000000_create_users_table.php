<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username')->index();
            $table->string('email')->unique();
            $table->string('alt_email');
            $table->string('phone');
            $table->integer('company');
            $table->string('password', 60);
            $table->date('expiry_date');
            $table->rememberToken();
            $table->timestamps();
            
//            $table->foreign('user_id')
//                    ->references('id')
//                    ->on('users')
//                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('users');
    }

}
