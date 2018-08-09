<?php

namespace App\Library\MWSCore\Products;


use App\Library\MWSCore\MWSConstant;
use App\Library\MWSCore\MWSCore;

class MWSProductsCore extends MWSCore {

	public function __construct( $appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken = false ) {
		parent::__construct( $appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken );
		$this->urlbranch          = 'Products/' . MWSConstant::AMAZON_VERSION_PRODUCTS;
		$this->options['Version'] = MWSConstant::AMAZON_VERSION_PRODUCTS;
	}

}