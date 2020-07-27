<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserHasRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_has_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            #User relation
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            #Role relation
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('role');

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
        Schema::dropIfExists('user_has_role');
    }
}
