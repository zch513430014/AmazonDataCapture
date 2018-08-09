<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMwsListOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mws_list_order_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('AppName');
            $table->index('AppName');

//            $table->string('FileName')->default('');
//            $table->string('NextToken')->default('');
            $table->string('LastUpdatedAfter')->default('');
            $table->string('LastUpdatedBefore')->default('');
//            $table->string('PageNo')->default('');
            $table->integer('Status')->default(0);
            $table->integer('FetchTime')->default(0);
            $table->integer('type')->default(0);
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
        Schema::dropIfExists('mws_list_order_logs');
    }
}
