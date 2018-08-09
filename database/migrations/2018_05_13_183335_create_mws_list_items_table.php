<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMwsListItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mws_list_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mwslistordersid');

            $table->integer('AppName')->default(0);
            $table->string('ASIN', 255)->default('');
            $table->string('OrderItemId', 255)->default('');
            $table->string('SellerSKU', 255)->default('');
            $table->text('BuyerCustomizedInfo');
            $table->text('Title');
            $table->string('QuantityOrdered', 255)->default('');
            $table->string('QuantityShipped', 255)->default('');
            $table->string('PointsGranted_PointsNumber', 255)->default('');
            $table->string('PointsGranted_PointsMonetaryValue_CurrencyCode', 255)->default('');
            $table->string('PointsGranted_PointsMonetaryValue_Amount', 255)->default('');
            $table->string('ItemPrice_CurrencyCode', 255)->default('')->default('');
            $table->string('ItemPrice_Amount', 255)->default('');
            $table->string('ShippingPrice_CurrencyCode', 255)->default('');
            $table->string('ShippingPrice_Amount', 255)->default('');
            $table->string('GiftWrapPrice_CurrencyCode', 255)->default('');
            $table->string('GiftWrapPrice_Amount', 255)->default('');
            $table->string('ItemTax_CurrencyCode', 255)->default('');
            $table->string('ItemTax_Amount', 255)->default('');
            $table->string('ShippingTax_CurrencyCode', 255)->default('');
            $table->string('ShippingTax_Amount', 255)->default('');
            $table->string('GiftWrapTax_CurrencyCode', 255)->default('');
            $table->string('GiftWrapTax_Amount', 255)->default('');
            $table->string('ShippingDiscount_CurrencyCode', 255)->default('');
            $table->string('ShippingDiscount_Amount', 255)->default('');
            $table->string('PromotionDiscount_CurrencyCode', 255)->default('');
            $table->string('PromotionDiscount_Amount', 255)->default('');
            $table->string('CODFee_CurrencyCode', 255)->default('');
            $table->string('CODFee_Amount', 255)->default('');
            $table->string('CODFeeDiscount_CurrencyCode', 255)->default('');
            $table->string('CODFeeDiscount_Amount', 255)->default('');
            $table->string('GiftMessageText', 255)->default('');
            $table->string('GiftWrapLevel', 255)->default('');
            $table->string('InvoiceData_InvoiceRequirement', 255)->default('');
            $table->string('InvoiceData_BuyerSelectedInvoiceCategory', 255)->default('');
            $table->string('InvoiceData_InvoiceTitle', 255)->default('');
            $table->string('InvoiceData_InvoiceInformation', 255)->default('');
            $table->string('ConditionNote', 255)->default('');
            $table->string('ConditionId', 255)->default('');
            $table->string('ConditionSubtypeId', 255)->default('');
            $table->string('ScheduledDeliveryStartDate', 255)->default('');
            $table->string('ScheduledDeliveryEndDate', 255)->default('');
            $table->string('PriceDesignation', 255)->default('');
            $table->integer('FetchTime')->default(0);
            $table->string('AmazonOrderId', 255)->default('');

            $table->index("mwslistordersid", "mwslistordersid");
            $table->index("OrderItemId", "OrderItemId");
            $table->index("FetchTime", "FetchTime");
            $table->index("AmazonOrderId", "AmazonOrderId");
            $table->index("ASIN", "ASIN");
            $table->index("SellerSKU", "SellerSKU");
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
        Schema::dropIfExists('mws_list_items');
    }
}
