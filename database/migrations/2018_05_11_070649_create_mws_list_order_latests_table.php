<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMWSListOrderLatestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mws_list_order_latests', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('AppName');
            $table->string('AmazonOrderId', 255)->default('')->nullable();
            $table->string('SellerOrderId', 255)->default('')->nullable();
            $table->string('PurchaseDate', 255)->default('')->nullable();
            $table->string('LastUpdateDate', 255)->default('')->nullable();
            $table->string('OrderStatus', 255)->default('')->nullable();
            $table->string('FulfillmentChannel', 255)->default('')->nullable();
            $table->string('SalesChannel', 255)->default('')->nullable();
            $table->string('OrderChannel', 255)->default('')->nullable();
            $table->string('ShipServiceLevel', 255)->default('')->nullable();
            $table->string('ShippingAddress_Name', 255)->default('')->nullable();
            $table->string('ShippingAddress_AddressLine1', 255)->default('')->nullable();
            $table->string('ShippingAddress_AddressLine2', 255)->default('')->nullable();
            $table->string('ShippingAddress_AddressLine3', 255)->default('')->nullable();
            $table->string('ShippingAddress_City', 255)->default('')->nullable();
            $table->string('ShippingAddress_County', 255)->default('')->nullable();
            $table->string('ShippingAddress_District', 255)->default('')->nullable();
            $table->string('ShippingAddress_StateOrRegion', 255)->default('')->nullable();
            $table->string('ShippingAddress_PostalCode', 255)->default('')->nullable();
            $table->string('ShippingAddress_CountryCode', 255)->default('')->nullable();
            $table->string('ShippingAddress_Phone', 255)->default('')->nullable();
            $table->string('OrderTotal_CurrencyCode', 255)->default('')->nullable();
            $table->string('OrderTotal_Amount', 255)->default('')->nullable();
            $table->string('NumberOfItemsShipped', 255)->default('')->nullable();
            $table->string('NumberOfItemsUnshipped', 255)->default('')->nullable();
            $table->string('PaymentExecutionDetail_Payment_CurrencyCode', 255)->default('')->nullable();
            $table->string('PaymentExecutionDetail_Payment_Amount', 255)->default('')->nullable();
            $table->string('PaymentExecutionDetail_SubPaymentMethod', 255)->default('')->nullable();
            $table->string('PaymentMethod', 255)->default('')->nullable();
            $table->string('MarketplaceId', 255)->default('')->nullable();
            $table->string('BuyerEmail', 255)->default('')->nullable();
            $table->string('BuyerName', 255)->default('')->nullable();
            $table->string('ShipmentServiceLevelCategory', 255)->default('')->nullable();
            $table->string('ShippedByAmazonTFM', 255)->default('')->nullable();
            $table->string('TFMShipmentStatus', 255)->default('')->nullable();
            $table->string('CbaDisplayableShippingLabel', 255)->default('')->nullable();
            $table->string('OrderType', 255)->default('')->nullable();
            $table->string('EarliestShipDate', 255)->default('')->nullable();
            $table->string('LatestShipDate', 255)->default('')->nullable();
            $table->string('EarliestDeliveryDate', 255)->default('')->nullable();
            $table->string('LatestDeliveryDate', 255)->default('')->nullable();
            $table->string('IsBusinessOrder', 255)->default('')->nullable();
            $table->string('PurchaseOrderNumber', 255)->default('')->nullable();
            $table->string('IsPrime', 255)->default('')->nullable();
            $table->string('IsPremiumOrder', 255)->default('')->nullable();
            $table->integer('FetchTime')->default(0)->nullable();
            $table->integer('isGetItem')->default(0)->nullable();
            $table->timestamps();

            $table->primary('id');
            $table->index('AmazonOrderId');
            $table->index('PurchaseDate');
            $table->index('LastUpdateDate');
            $table->index('BuyerEmail');
            $table->index('FetchTime');
            $table->index('LatestShipDate');
            $table->index('isGetItem');
            $table->index('AppName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mws_list_order_latests');
    }
}
