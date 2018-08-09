<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMwsMarketplacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mws_marketplaces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('SellerId', false);
            $table->string('MarketplaceId', 255);
            $table->string('DefaultCountryCode', 255);
            $table->string('DomainName', 255);
            $table->string('Name', 255);
            $table->string('DefaultCurrencyCode', 255);
            $table->string('DefaultLanguageCode', 255);
            $table->index('SellerId');
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
        Schema::dropIfExists('mws_marketplaces');
    }
}
