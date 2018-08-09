<?php
/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/28
 * Time: 10:32
 */

namespace App\Library\MWSCore\Products;

use App\Library\MWSCore\MWSConstant;

class MWSProductInfo extends MWSProductsCore {

	public function GetMyPriceForASIN() {
		if ( ! array_key_exists( 'SellerSKUList.SellerSKU.1', $this->options ) && ! array_key_exists( 'ASINList.ASIN.1', $this->options ) ) {
			$this->log( 'warning', "Product IDs must be set in order to look them up!" );

			return false;
		}
		$this->options['Action'] = 'GetMyPriceForASIN';
		$this->prepareMyPrice();
		$param    = $this->genQuery();
		$response = $this->sendRequest( $param );

	}

	/**
	 * Sets the ASIN(s). (Required*)
	 *
	 * This method sets the list of ASINs to be sent in the next request.
	 * Setting this parameter tells Amazon to only return inventory supplies that match
	 * the IDs in the list. If this parameter is set, Seller SKUs cannot be set.
	 *
	 * @param array|string $s <p>A list of ASINs, or a single ASIN string. (max: 20)</p>
	 *
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setASINs( $s ) {
		if ( is_string( $s ) ) {
			$this->resetASINs();
			$this->options['ASINList.ASIN.1'] = $s;
		} else if ( is_array( $s ) ) {
			$this->resetASINs();
			$i = 1;
			foreach ( $s as $x ) {
				$this->options[ 'ASINList.ASIN.' . $i ] = $x;
				$i ++;
			}
		} else {
			return false;
		}
	}

	/**
	 * Resets the ASIN options.
	 *
	 * Since ASIN is a required parameter, these options should not be removed
	 * without replacing them, so this method is not public.
	 */
	protected function resetASINs() {
		foreach ( $this->options as $op => $junk ) {
			if ( preg_match( "#ASINList#", $op ) ) {
				unset( $this->options[ $op ] );
			}
		}
		//remove Category-specific name
		unset( $this->options['ASIN'] );
	}

	/**
	 * Sets up options for using <i>fetchMyPrice</i>.
	 *
	 * This changes key options for using <i>fetchMyPrice</i>.
	 * Please note: because the operation does not use all of the parameters,
	 * the ExcludeMe parameter will be removed.
	 */
	protected function prepareMyPrice() {
		$this->throttleTime = MWSConstant::THROTTLE_TIME_PRODUCTPRICE;
	}


}