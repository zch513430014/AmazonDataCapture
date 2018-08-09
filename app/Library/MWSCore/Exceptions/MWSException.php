<?php
/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/12
 * Time: 15:01
 */

namespace App\Library\MWSCore\Exceptions;



use Throwable;

class MWSException extends \Exception {

	public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}