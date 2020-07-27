<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleHasPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_has_permission', function (Blueprint $table) {
            $table->bigIncrements('id');

            # Permission relation
            $table->integer('permission_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permission');

            # Role relation
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
        Schema::dropIfExists('role_has_permission');
    }
}
