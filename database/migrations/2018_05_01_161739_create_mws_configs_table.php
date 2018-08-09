<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMwsConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mws_configs', function (Blueprint $table) {
            $table->increments('id')->comment('这个Id用于之后的AppName命名');
            $table->string('SellerId', 255)->comment('也可以叫做MerchantId');
            $table->string('MWSAWSAccessKeyId', 255);
            $table->string('MWSSecretKey', 255);
            $table->string('ServiceUrl', 255)->default('');
            $table->integer('Status')->default(0);
            $table->string('Remark', 255)->nullable()->default('');
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
        Schema::dropIfExists('mws_configs');
    }
}
