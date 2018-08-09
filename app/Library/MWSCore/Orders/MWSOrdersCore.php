<?php
/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/12
 * Time: 15:14
 */

namespace App\Library\MWSCore\Orders;

use App\Library\MWSCore\MWSConstant;
use App\Library\MWSCore\MWSCore;
use App\Library\MWSCore\MWSLogger;

/**
 * Document:http://docs.developer.amazonservices.com/en_US/orders-2013-09-01/Orders_Overview.html
 * Class MWSOrdersCore
 * @package App\Library\MWSCore\Orders
 */
abstract class MWSOrdersCore extends MWSCore {

	public function __construct( $appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken = false ) {
		parent::__construct( $appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken );
		$this->urlbranch          = 'Orders/'.MWSConstant::AMAZON_VERSION_ORDERS;
		$this->options['Version'] = MWSConstant::AMAZON_VERSION_ORDERS;

	}
}