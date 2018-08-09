<?php
/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/6
 * Time: 10:50
 */

namespace App\Library\MWSCore;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class MWSLogger {
	const LOG_ERROR = 'error';

	private static $loggers = array();

	// 获取一个实例
	public static function getLogger( $type = self::LOG_ERROR, $day = 30 ) {

		return self::$loggers[ $type ] = new Logger($type) ;
	}
}