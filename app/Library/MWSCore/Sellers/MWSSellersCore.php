<?php
/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/6
 * Time: 11:42
 */

namespace App\Library\MWSCore\Sellers;

use App\Library\MWSCore\MWSCore;
use App\Library\MWSCore\MWSLogger;
use Hamcrest\Thingy;

class MWSSellersCore extends MWSCore {

	public function __construct( $appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken = false ) {
		parent::__construct( $appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken );
		$this->urlbranch          = 'Sellers/2011-07-01';
		$this->options['Version'] = '2011-07-01';
	}

	public function ListMarketplaceParticipations() {
		$this->options['Action'] = 'ListMarketplaceParticipations';
		$param                   = $this->genQuery();
		$resopnse                = $this->sendRequest( $param );

		return $resopnse;
	}


	public function ListMarketplaceParticipationsByNextToken( $token ) {

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
     * 获取当前商家是否可行
     */
    public function getServiceStatus()
    {
        $this->options['Action'] = 'GetServiceStatus';
        $param = $this->genQuery();
        $response = $this->sendRequest($param);
        return $response;
	}
}