<?php
/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/22
 * Time: 10:12
 */

namespace App\Library\MWSCore\Orders;

use App\Library\MWSCore\MWSConstant;
use App\Library\MWSCore\MWSLogger;
use Illuminate\Support\Facades\Log;

class MWSOrderList extends MWSOrdersCore
{
    protected $orderList;
    protected $i = 0;
    protected $tokenFlag = false;
    protected $tokenUseFlag = false;
    protected $index = 0;

    public function __construct($appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, bool $MWSAuthonToken = false)
    {
        parent::__construct($appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken);
        $this->throttleTime = MWSConstant::THROTTLE_TIME_ORDER;
    }

    public function ListOrders($continue = true)
    {
//		$xmls = new \SimpleXMLElement( file_get_contents( storage_path( 'app\public\mock\Orders\ListOrdersResponse.xml' ) ) );
        if (!array_key_exists('CreatedAfter', $this->options) && !array_key_exists('LastUpdatedAfter', $this->options)) {
            $this->setLimits('Created', '2017-01-01', '2017-01-02');
        }
        $this->options['Action'] = 'ListOrders';
        $this->prepareToken();
        if ($this->tokenFlag) {
            $this->resetMarketplaceFilter();
        }
        $param = $this->genQuery();
        $response = $this->sendRequest($param);
        $path = $this->options['Action'] . 'Result';
        if (!$this->checkResponse($response)) {
            return false;
        }
        $xml = simplexml_load_string($response['body'])->$path;
        $this->parseXML($xml);
        $this->checkToken($xml);
        if ($this->tokenFlag && $this->tokenUseFlag && $continue === true) {
            while ($this->tokenFlag) {
                $this->log('info', "Recursively fetching more Participationseses");
                $this->ListOrders(false);
            }
        }

        return $response;
    }

    public function setUserToken($tokenUseFlag = true)
    {
        $this->tokenUseFlag = $tokenUseFlag;
    }

    public function prepareToken()
    {
        if ($this->tokenFlag && $this->tokenUseFlag) {
            $this->options['Action'] = 'ListOrdersByNextToken';

            //When using tokens, only the NextToken option should be used
            unset($this->options['SellerOrderId']);
            $this->resetOrderStatusFilter();
            $this->resetPaymentMethodFilter();
            $this->setFulfillmentChannelFilter(null);
            $this->setSellerOrderIdFilter(null);
            $this->setEmailFilter(null);
            unset($this->options['LastUpdatedAfter']);
            unset($this->options['LastUpdatedBefore']);
            unset($this->options['CreatedAfter']);
            unset($this->options['CreatedBefore']);
            unset($this->options['MaxResultsPerPage']);

        } else {
            $this->options['Action'] = 'ListOrders';
            unset($this->options['NextToken']);
            $this->index = 0;
            $this->orderList = array();
        }

    }

    public function parseXML(\SimpleXMLElement $xml)
    {

        if (!$xml) {
            return false;
        }
        foreach ($xml->Orders->children() as $key => $data) {
            if ($key != 'Order') {
                break;
            }
            $d = array();
            $d['AmazonOrderId'] = (string)$data->AmazonOrderId;
            if (isset($data->SellerOrderId)) {
                $d['SellerOrderId'] = (string)$data->SellerOrderId;
            }
            $d['PurchaseDate'] = (string)$data->PurchaseDate;
            $d['LastUpdateDate'] = (string)$data->LastUpdateDate;
            $d['OrderStatus'] = (string)$data->OrderStatus;
            if (isset($data->FulfillmentChannel)) {
                $d['FulfillmentChannel'] = (string)$data->FulfillmentChannel;
            }
            if (isset($data->SalesChannel)) {
                $d['SalesChannel'] = (string)$data->SalesChannel;
            }
            if (isset($data->OrderChannel)) {
                $d['OrderChannel'] = (string)$data->OrderChannel;
            }
            if (isset($data->ShipServiceLevel)) {
                $d['ShipServiceLevel'] = (string)$data->ShipServiceLevel;
            }
            if (isset($data->ShippingAddress)) {
                $d['ShippingAddress'] = array();
                $d['ShippingAddress']['Name'] = (string)$data->ShippingAddress->Name;
                $d['ShippingAddress']['AddressLine1'] = (string)$data->ShippingAddress->AddressLine1;
                $d['ShippingAddress']['AddressLine2'] = (string)$data->ShippingAddress->AddressLine2;
                $d['ShippingAddress']['AddressLine3'] = (string)$data->ShippingAddress->AddressLine3;
                $d['ShippingAddress']['City'] = (string)$data->ShippingAddress->City;
                $d['ShippingAddress']['County'] = (string)$data->ShippingAddress->County;
                $d['ShippingAddress']['District'] = (string)$data->ShippingAddress->District;
                $d['ShippingAddress']['StateOrRegion'] = (string)$data->ShippingAddress->StateOrRegion;
                $d['ShippingAddress']['PostalCode'] = (string)$data->ShippingAddress->PostalCode;
                $d['ShippingAddress']['CountryCode'] = (string)$data->ShippingAddress->CountryCode;
                $d['ShippingAddress']['Phone'] = (string)$data->ShippingAddress->Phone;
            }
            if (isset($data->OrderTotal)) {
                $d['OrderTotal'] = array();
                $d['OrderTotal']['Amount'] = (string)$data->OrderTotal->Amount;
                $d['OrderTotal']['CurrencyCode'] = (string)$data->OrderTotal->CurrencyCode;
            }
            if (isset($data->NumberOfItemsShipped)) {
                $d['NumberOfItemsShipped'] = (string)$data->NumberOfItemsShipped;
            }
            if (isset($data->NumberOfItemsUnshipped)) {
                $d['NumberOfItemsUnshipped'] = (string)$data->NumberOfItemsUnshipped;
            }
            if (isset($data->PaymentExecutionDetail)) {
                $d['PaymentExecutionDetail'] = array();

                $i = 0;
                foreach ($data->PaymentExecutionDetail->children() as $x) {
                    $d['PaymentExecutionDetail'][$i]['Amount'] = (string)$x->Payment->Amount;
                    $d['PaymentExecutionDetail'][$i]['CurrencyCode'] = (string)$x->Payment->CurrencyCode;
                    $d['PaymentExecutionDetail'][$i]['SubPaymentMethod'] = (string)$x->SubPaymentMethod;
                    $i++;
                }
            }
            if (isset($data->PaymentMethod)) {
                $d['PaymentMethod'] = (string)$data->PaymentMethod;
            }
            $d['MarketplaceId'] = (string)$data->MarketplaceId;
            if (isset($data->BuyerName)) {
                $d['BuyerName'] = (string)$data->BuyerName;
            }
            if (isset($data->BuyerEmail)) {
                $d['BuyerEmail'] = (string)$data->BuyerEmail;
            }
            if (isset($data->ShipmentServiceLevelCategory)) {
                $d['ShipmentServiceLevelCategory'] = (string)$data->ShipmentServiceLevelCategory;
            }
            if (isset($data->CbaDisplayableShippingLabel)) {
                $d['CbaDisplayableShippingLabel'] = (string)$data->CbaDisplayableShippingLabel;
            }
            if (isset($data->ShippedByAmazonTFM)) {
                $d['ShippedByAmazonTFM'] = (string)$data->ShippedByAmazonTFM;
            }
            if (isset($data->TFMShipmentStatus)) {
                $d['TFMShipmentStatus'] = (string)$data->TFMShipmentStatus;
            }
            if (isset($data->OrderType)) {
                $d['OrderType'] = (string)$data->OrderType;
            }
            if (isset($data->EarliestShipDate)) {
                $d['EarliestShipDate'] = (string)$data->EarliestShipDate;
            }
            if (isset($data->LatestShipDate)) {
                $d['LatestShipDate'] = (string)$data->LatestShipDate;
            }
            if (isset($data->EarliestDeliveryDate)) {
                $d['EarliestDeliveryDate'] = (string)$data->EarliestDeliveryDate;
            }
            if (isset($data->LatestDeliveryDate)) {
                $d['LatestDeliveryDate'] = (string)$data->LatestDeliveryDate;
            }
            if (isset($data->IsBusinessOrder)) {
                $d['IsBusinessOrder'] = (string)$data->IsBusinessOrder;
            }
            if (isset($data->PurchaseOrderNumber)) {
                $d['PurchaseOrderNumber'] = (string)$data->PurchaseOrderNumber;
            }
            if (isset($data->IsPrime)) {
                $d['IsPrime'] = (string)$data->IsPrime;
            }
            if (isset($data->IsPremiumOrder)) {
                $d['IsPremiumOrder'] = (string)$data->IsPremiumOrder;
            }
            $this->orderList[$this->index] = $d;
            $this->index++;
        }

    }

    /**
     * Returns the list of orders.
     *
     * @return array|boolean array of <i>AmazonOrder</i> objects, or <b>FALSE</b> if list not filled yet
     */
    public function getList()
    {
        if (isset($this->orderList)) {
            return $this->orderList;
        } else {
            return false;
        }

    }

    /**
     * Sets the time frame for the orders fetched. (Optional)
     *
     * Sets the time frame for the orders fetched. If no times are specified, times default to the current time.
     *
     * @param string $mode  <p>"Created" or "Modified"</p>
     * @param string $lower [optional] <p>A time string for the earliest time.</p>
     * @param string $upper [optional] <p>A time string for the latest time.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setLimits($mode, $lower = null, $upper = null)
    {
        try {
            if ($upper) {
                $before = $this->genTime($upper);
            } else {
                $before = $this->genTime('- 2 min');
            }
            if ($lower) {
                $after = $this->genTime($lower);
            } else {
                $after = $this->genTime('- 2 min');
            }
            if ($after > $before) {
                $after = $this->genTime($upper . ' - 150 sec');
            }
            if ($mode == 'Created') {
                $this->options['CreatedAfter'] = $after;
                if ($before) {
                    $this->options['CreatedBefore'] = $before;
                }
                unset($this->options['LastUpdatedAfter']);
                unset($this->options['LastUpdatedBefore']);
            } else if ($mode == 'Modified') {
                $this->options['LastUpdatedAfter'] = $after;
                if ($before) {
                    $this->options['LastUpdatedBefore'] = $before;
                }
                unset($this->options['CreatedAfter']);
                unset($this->options['CreatedBefore']);
            } else {
                $this->log('warning', 'First parameter should be either "Created" or "Modified".');
                return false;
            }

        } catch (\Exception $e) {
            $this->log('error', 'Error: ' . $e->getMessage() . $this->options['LastUpdatedAfter'] . $this->options['LastUpdatedBefore'] . $e->getFile() . $e->getLine());
            return false;
        }

    }

    /**
     * Sets the order status(es). (Optional)
     *
     * This method sets the list of Order Statuses to be sent in the next request.
     * Setting this parameter tells Amazon to only return Orders with statuses that match
     * those in the list. If this parameter is not set, Amazon will return
     * Orders of any status.
     *
     * @param array|string $list <p>A list of Order Statuses, or a single status string.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setOrderStatusFilter($list)
    {
        if (is_string($list)) {
            //if single string, set as filter
            $this->resetOrderStatusFilter();
            $this->options['OrderStatus.Status.1'] = $list;
        } else if (is_array($list)) {
            //if array of strings, set all filters
            $this->resetOrderStatusFilter();
            $i = 1;
            foreach ($list as $x) {
                $this->options['OrderStatus.Status.' . $i] = $x;
                $i++;
            }
        } else {
            return false;
        }
    }

    /**
     * Removes order status options.
     *
     * Use this in case you change your mind and want to remove the Order Status
     * parameters you previously set.
     */
    public function resetOrderStatusFilter()
    {
        foreach ($this->options as $op => $junk) {
            if (preg_match("#OrderStatus#", $op)) {
                unset($this->options[$op]);
            }
        }
    }

    /**
     * Sets the marketplace(s). (Optional)
     *
     * This method sets the list of Marketplaces to be sent in the next request.
     * Setting this parameter tells Amazon to only return Orders made in marketplaces that match
     * those in the list. If this parameter is not set, Amazon will return
     * Orders belonging to the current store's default marketplace.
     *
     * @param array|string $list <p>A list of Marketplace IDs, or a single Marketplace ID.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setMarketplaceFilter($list)
    {
        if (is_string($list)) {
            //if single string, set as filter
            $this->resetMarketplaceFilter();
            $this->options['MarketplaceId.Id.1'] = $list;
        } else if (is_array($list)) {
            //if array of strings, set all filters
            $this->resetMarketplaceFilter();
            $i = 1;
            foreach ($list as $x) {
                $this->options['MarketplaceId.Id.' . $i] = $x;
                $i++;
            }
        } else {
            return false;
        }
    }

    /**
     * Removes marketplace ID options and sets the current store's marketplace instead.
     *
     * Use this in case you change your mind and want to remove the Marketplace ID
     * parameters you previously set.
     *
     * @throws \Exception if config file is missing
     */
    public function resetMarketplaceFilter()
    {
        foreach ($this->options as $op => $junk) {
            if (preg_match("#MarketplaceId#", $op)) {
                unset($this->options[$op]);
            }
        }
    }

    /**
     * Sets (or resets) the Fulfillment Channel Filter
     *
     * @param string $filter <p>'AFN' or 'MFN' or NULL</p>
     *
     * @return boolean <b>FALSE</b> on failure
     */
    public function setFulfillmentChannelFilter($filter)
    {
        if ($filter == 'AFN' || $filter == 'MFN') {
            $this->options['FulfillmentChannel.Channel.1'] = $filter;
        } else if (is_null($filter)) {
            unset($this->options['FulfillmentChannel.Channel.1']);
        } else {
            return false;
        }
    }

    /**
     * Sets the payment method(s). (Optional)
     *
     * This method sets the list of Payment Methods to be sent in the next request.
     * Setting this parameter tells Amazon to only return Orders with payment methods
     * that match those in the list. If this parameter is not set, Amazon will return
     * Orders with any payment method.
     *
     * @param array|string $list <p>A list of Payment Methods, or a single method string.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setPaymentMethodFilter($list)
    {
        if (is_string($list)) {
            //if single string, set as filter
            $this->resetPaymentMethodFilter();
            $this->options['PaymentMethod.1'] = $list;
        } else if (is_array($list)) {
            //if array of strings, set all filters
            $this->resetPaymentMethodFilter();
            $i = 1;
            foreach ($list as $x) {
                $this->options['PaymentMethod.' . $i++] = $x;
            }
        } else {
            return false;
        }
    }

    /**
     * Removes payment method options.
     *
     * Use this in case you change your mind and want to remove the Payment Method
     * parameters you previously set.
     */
    public function resetPaymentMethodFilter()
    {
        foreach ($this->options as $op => $junk) {
            if (preg_match("#PaymentMethod#", $op)) {
                unset($this->options[$op]);
            }
        }
    }

    /**
     * Sets (or resets) the email address. (Optional)
     *
     * This method sets the email address to be sent in the next request.
     * Setting this parameter tells Amazon to only return Orders with email addresses
     * that match the email address given. If this parameter is set, the following options
     * will be removed: SellerOrderId, OrderStatus, PaymentMethod, FulfillmentChannel, LastUpdatedAfter, LastUpdatedBefore.
     *
     * @param string $filter <p>A single email address string. Set to NULL to remove the option.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setEmailFilter($filter)
    {
        if (is_string($filter)) {
            $this->options['BuyerEmail'] = $filter;
            //these fields must be disabled
            unset($this->options['SellerOrderId']);
            $this->resetOrderStatusFilter();
            $this->resetPaymentMethodFilter();
            $this->setFulfillmentChannelFilter(null);
            unset($this->options['LastUpdatedAfter']);
            unset($this->options['LastUpdatedBefore']);
        } else if (is_null($filter)) {
            unset($this->options['BuyerEmail']);
        } else {
            return false;
        }
    }

    /**
     * Sets (or resets) the seller order ID(s). (Optional)
     *
     * This method sets the list of seller order ID to be sent in the next request.
     * Setting this parameter tells Amazon to only return Orders with seller order IDs
     * that match the seller order ID given. If this parameter is set, the following options
     * will be removed: BuyerEmail, OrderStatus, PaymentMethod, FulfillmentChannel, LastUpdatedAfter, LastUpdatedBefore.
     *
     * @param array|string $filter <p>A single seller order ID. Set to NULL to remove the option.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setSellerOrderIdFilter($filter)
    {
        if (is_string($filter)) {
            $this->options['SellerOrderId'] = $filter;
            //these fields must be disabled
            unset($this->options['BuyerEmail']);
            $this->resetOrderStatusFilter();
            $this->resetPaymentMethodFilter();
            $this->setFulfillmentChannelFilter(null);
            unset($this->options['LastUpdatedAfter']);
            unset($this->options['LastUpdatedBefore']);
        } else if (is_null($filter)) {
            unset($this->options['SellerOrderId']);
        } else {
            return false;
        }
    }

    /**
     * Sets the maximum response per page count. (Optional)
     *
     * This method sets the maximum number of Feed Submissions for Amazon to return per page.
     * If this parameter is not set, Amazon will send 100 at a time.
     *
     * @param array|string $num <p>Positive integer from 1 to 100.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setMaxResultsPerPage($num)
    {
        if (is_int($num) && $num <= 100 && $num >= 1) {
            $this->options['MaxResultsPerPage'] = $num;
        } else {
            return false;
        }
    }

    /**
     * Sets the TFM shipment status(es). (Optional)
     *
     * This method sets the list of TFM Shipment Statuses to be sent in the next request.
     * Setting this parameter tells Amazon to only return TFM Orders with statuses that match
     * those in the list. If this parameter is not set, Amazon will return
     * Orders of any status, including non-TFM orders.
     *
     * @param array|string $list <p>A list of TFM Shipment Statuses, or a single status string.</p>
     *
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setTfmShipmentStatusFilter($list)
    {
        if (is_string($list)) {
            //if single string, set as filter
            $this->resetTfmShipmentStatusFilter();
            $this->options['TFMShipmentStatus.Status.1'] = $list;
        } else if (is_array($list)) {
            //if array of strings, set all filters
            $this->resetTfmShipmentStatusFilter();
            $i = 1;
            foreach ($list as $x) {
                $this->options['TFMShipmentStatus.Status.' . $i] = $x;
                $i++;
            }
        } else {
            return false;
        }
    }

    /**
     * Removes order status options.
     *
     * Use this in case you change your mind and want to remove the TFM Shipment Status
     * parameters you previously set.
     */
    public function resetTfmShipmentStatusFilter()
    {
        foreach ($this->options as $op => $junk) {
            if (preg_match("#TFMShipmentStatus#", $op)) {
                unset($this->options[$op]);
            }
        }
    }

}
