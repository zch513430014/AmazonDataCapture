<?php

/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/5/14
 * Time: 2:11
 */

namespace App\Library\MWSCore\Orders;

use App\Library\MWSCore\MWSConstant;

class MWSOrderItemList extends MWSOrdersCore
{

    protected $orderId;
    protected $itemList;
    protected $tokenFlag = false;
    protected $tokenUseFlag = false;
    protected $i = 0;
    protected $index = 0;

    public function __construct($appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $orderId, bool $MWSAuthonToken = false)
    {
        parent::__construct($appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken);
        $this->orderId = $orderId;
        $this->options['AmazonOrderId'] = $orderId;
        $this->throttleTime = MWSConstant::THROTTLE_LIMIT_ITEM;
    }

    /**
     * Sets up options for using tokens.
     *
     * This changes key options for switching between simply fetching a list and
     * fetching the rest of a list using a token. Please note: because the
     * operation for using tokens does not use any other parameters, all other
     * parameters will be removed.
     */
    protected function prepareToken()
    {
        if ($this->tokenFlag && $this->tokenUseFlag) {
            $this->options['Action'] = 'ListOrderItemsByNextToken';
            //When using tokens, only the NextToken option should be used
            unset($this->options['AmazonOrderId']);
        } else {
            $this->options['Action'] = 'ListOrderItems';
            unset($this->options['NextToken']);
            $this->index = 0;
            $this->itemList = array();
        }
    }

    public function ListOrderItems($continue = true)
    {
        $this->prepareToken();
        $this->options['Action'] = 'ListOrderItems';
        $param = $this->genQuery();
        $path = $this->options['Action'] . 'Result';
        $response = $this->sendRequest($param);
        if (!$this->checkResponse($response)) {
            return false;
        }
        $xml = simplexml_load_string($response['body'])->$path;
        $this->parseXML($xml->OrderItems);
        $this->checkToken($xml);
        if ($this->tokenFlag && $this->tokenUseFlag && $continue === true) {
            while ($this->tokenFlag) {
                $this->log('info', "Recursively fetching more Participationseses");
                $this->ListOrderItems(false);
            }
        }
        return $response;
    }


    public function parseXML(\SimpleXMLElement $xml)
    {

        if (!$xml) {
            return false;
        }

        foreach ($xml->children() as $item) {
            $n = $this->index;

            $this->itemList[$n]['ASIN'] = (string)$item->ASIN;
            $this->itemList[$n]['SellerSKU'] = (string)$item->SellerSKU;
            $this->itemList[$n]['OrderItemId'] = (string)$item->OrderItemId;
            $this->itemList[$n]['Title'] = (string)$item->Title;
            $this->itemList[$n]['QuantityOrdered'] = (string)$item->QuantityOrdered;
            if (isset($item->QuantityShipped)) {
                $this->itemList[$n]['QuantityShipped'] = (string)$item->QuantityShipped;
            }
            if (isset($item->BuyerCustomizedInfo->CustomizedURL)) {
                $this->itemList[$n]['BuyerCustomizedInfo'] = (string)$item->BuyerCustomizedInfo->CustomizedURL;
            }
            if (isset($item->PointsGranted)) {
                $this->itemList[$n]['PointsGranted']['PointsNumber'] = (string)$item->PointsGranted->PointsNumber;
                $this->itemList[$n]['PointsGranted']['Amount'] = (string)$item->PointsGranted->PointsMonetaryValue->Amount;
                $this->itemList[$n]['PointsGranted']['CurrencyCode'] = (string)$item->PointsGranted->PointsMonetaryValue->CurrencyCode;
            }
            if (isset($item->PriceDesignation)) {
                $this->itemList[$n]['PriceDesignation'] = (string)$item->PriceDesignation;
            }
            if (isset($item->GiftMessageText)) {
                $this->itemList[$n]['GiftMessageText'] = (string)$item->GiftMessageText;
            }
            if (isset($item->GiftWrapLevel)) {
                $this->itemList[$n]['GiftWrapLevel'] = (string)$item->GiftWrapLevel;
            }
            if (isset($item->ItemPrice)) {
                $this->itemList[$n]['ItemPrice']['Amount'] = (string)$item->ItemPrice->Amount;
                $this->itemList[$n]['ItemPrice']['CurrencyCode'] = (string)$item->ItemPrice->CurrencyCode;
            }
            if (isset($item->ShippingPrice)) {
                $this->itemList[$n]['ShippingPrice']['Amount'] = (string)$item->ShippingPrice->Amount;
                $this->itemList[$n]['ShippingPrice']['CurrencyCode'] = (string)$item->ShippingPrice->CurrencyCode;
            }
            if (isset($item->GiftWrapPrice)) {
                $this->itemList[$n]['GiftWrapPrice']['Amount'] = (string)$item->GiftWrapPrice->Amount;
                $this->itemList[$n]['GiftWrapPrice']['CurrencyCode'] = (string)$item->GiftWrapPrice->CurrencyCode;
            }
            if (isset($item->ItemTax)) {
                $this->itemList[$n]['ItemTax']['Amount'] = (string)$item->ItemTax->Amount;
                $this->itemList[$n]['ItemTax']['CurrencyCode'] = (string)$item->ItemTax->CurrencyCode;
            }
            if (isset($item->ShippingTax)) {
                $this->itemList[$n]['ShippingTax']['Amount'] = (string)$item->ShippingTax->Amount;
                $this->itemList[$n]['ShippingTax']['CurrencyCode'] = (string)$item->ShippingTax->CurrencyCode;
            }
            if (isset($item->GiftWrapTax)) {
                $this->itemList[$n]['GiftWrapTax']['Amount'] = (string)$item->GiftWrapTax->Amount;
                $this->itemList[$n]['GiftWrapTax']['CurrencyCode'] = (string)$item->GiftWrapTax->CurrencyCode;
            }
            if (isset($item->ShippingDiscount)) {
                $this->itemList[$n]['ShippingDiscount']['Amount'] = (string)$item->ShippingDiscount->Amount;
                $this->itemList[$n]['ShippingDiscount']['CurrencyCode'] = (string)$item->ShippingDiscount->CurrencyCode;
            }
            if (isset($item->PromotionDiscount)) {
                $this->itemList[$n]['PromotionDiscount']['Amount'] = (string)$item->PromotionDiscount->Amount;
                $this->itemList[$n]['PromotionDiscount']['CurrencyCode'] = (string)$item->PromotionDiscount->CurrencyCode;
            }
            if (isset($item->CODFee)) {
                $this->itemList[$n]['CODFee']['Amount'] = (string)$item->CODFee->Amount;
                $this->itemList[$n]['CODFee']['CurrencyCode'] = (string)$item->CODFee->CurrencyCode;
            }
            if (isset($item->CODFeeDiscount)) {
                $this->itemList[$n]['CODFeeDiscount']['Amount'] = (string)$item->CODFeeDiscount->Amount;
                $this->itemList[$n]['CODFeeDiscount']['CurrencyCode'] = (string)$item->CODFeeDiscount->CurrencyCode;
            }
            if (isset($item->PromotionIds)) {
                $i = 0;
                foreach ($item->PromotionIds->children() as $x) {
                    $this->itemList[$n]['PromotionIds'][$i] = (string)$x;
                    $i++;
                }
            }
            if (isset($item->InvoiceData)) {
                if (isset($item->InvoiceData->InvoiceRequirement)) {
                    $this->itemList[$n]['InvoiceData']['InvoiceRequirement'] = (string)$item->InvoiceData->InvoiceRequirement;
                }
                if (isset($item->InvoiceData->BuyerSelectedInvoiceCategory)) {
                    $this->itemList[$n]['InvoiceData']['BuyerSelectedInvoiceCategory'] = (string)$item->InvoiceData->BuyerSelectedInvoiceCategory;
                }
                if (isset($item->InvoiceData->InvoiceTitle)) {
                    $this->itemList[$n]['InvoiceData']['InvoiceTitle'] = (string)$item->InvoiceData->InvoiceTitle;
                }
                if (isset($item->InvoiceData->InvoiceInformation)) {
                    $this->itemList[$n]['InvoiceData']['InvoiceInformation'] = (string)$item->InvoiceData->InvoiceInformation;
                }
            }
            if (isset($item->ConditionId)) {
                $this->itemList[$n]['ConditionId'] = (string)$item->ConditionId;
            }
            if (isset($item->ConditionSubtypeId)) {
                $this->itemList[$n]['ConditionSubtypeId'] = (string)$item->ConditionSubtypeId;
            }
            if (isset($item->ConditionNote)) {
                $this->itemList[$n]['ConditionNote'] = (string)$item->ConditionNote;
            }
            if (isset($item->ScheduledDeliveryStartDate)) {
                $this->itemList[$n]['ScheduledDeliveryStartDate'] = (string)$item->ScheduledDeliveryStartDate;
            }
            if (isset($item->ScheduledDeliveryEndDate)) {
                $this->itemList[$n]['ScheduledDeliveryEndDate'] = (string)$item->ScheduledDeliveryEndDate;
            }
            $this->index++;
        }
    }

    /**
     * Returns the specified order item, or all of them.
     *
     * This method will return <b>FALSE</b> if the list has not yet been filled.
     * The array for a single order item will have the following fields:
     * <ul>
     * <li><b>ASIN</b> - the ASIN for the item</li>
     * <li><b>SellerSKU</b> - the SKU for the item</li>
     * <li><b>OrderItemId</b> - the unique ID for the order item</li>
     * <li><b>Title</b> - the name of the item</li>
     * <li><b>QuantityOrdered</b> - the quantity of the item ordered</li>
     * <li><b>QuantityShipped</b> (optional) - the quantity of the item shipped</li>
     * <li><b>GiftMessageText</b> (optional) - gift message for the item</li>
     * <li><b>GiftWrapLevel</b> (optional) - the type of gift wrapping for the item</li>
     * <li><b>ItemPrice</b> (optional) - price for the item, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>ShippingPrice</b> (optional) - price for shipping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>GiftWrapPrice</b> (optional) - price for gift wrapping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>ItemTax</b> (optional) - tax on the item, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>ShippingTax</b> (optional) - tax on shipping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>GiftWrapTax</b> (optional) - tax on gift wrapping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>ShippingDiscount</b> (optional) - discount on shipping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>PromotionDiscount</b> (optional) -promotional discount, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>CODFee</b> (optional) -fee charged for COD service, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>CODFeeDiscount</b> (optional) -discount on COD fee, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
     * <li><b>PromotionIds</b> (optional) -array of promotion IDs</li>
     * </ul>
     * @param int $i [optional] <p>List index to retrieve the value from.
     * If none is given, the entire list will be returned. Defaults to NULL.</p>
     * @return array|boolean array, multi-dimensional array, or <b>FALSE</b> if list not filled yet
     */
    public function getItems($i = null)
    {
        if (isset($this->itemList)) {
            if (is_numeric($i)) {
                return $this->itemList[$i];
            } else {
                return $this->itemList;
            }
        } else {
            return false;
        }
    }

}