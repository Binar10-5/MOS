<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModuleIdToPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permission', function (Blueprint $table) {
            # Module relation
            $table->integer('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('module');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission', function (Blueprint $table) {
            //
        });
    }
}
