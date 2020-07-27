<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleHasModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_has_module', function (Blueprint $table) {
            $table->bigIncrements('id');

            #Module relation
            $table->integer('parent_module_id')->unsigned();
            $table->foreign('parent_module_id')->references('id')->on('module');
            #Module relation
            $table->integer('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('module');

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
        Schema::dropIfExists('module_has_module');
    }
}
