<?php

/**
 * Created by PhpStorm.
 * User: GrantZuo
 * Date: 2018/2/5
 * Time: 11:43
 */

namespace App\Library\MWSCore;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use Monolog\Logger;

abstract class MWSCore
{
    protected $urlBase;//该字段决定站点的使用
    protected $options;//请求时将用到的变量
    protected $AppName;//标记当前程序的名字
    protected $logType;
    protected $urlbranch;
    protected $SecreKey;
    protected $rawResponses;
    protected $throttleStop = true;
    protected $requestParameters;
    protected $throttleTime;//休眠时间


    public function __construct($appName, $SellerId, $AccessKey, $SecreKey, $serviceUrl, $logType, $MWSAuthonToken = false)
    {
        $this->urlBase = rtrim($serviceUrl, '/') . '/';
        $this->options['SellerId'] = $SellerId;
        $this->options['AWSAccessKeyId'] = $AccessKey;
        $this->SecreKey = $SecreKey;
        $this->logType = $logType;
        if ($MWSAuthonToken) {
            $this->options['MWSAuthToken'] = $MWSAuthonToken;
        }
        $this->options['SignatureVersion'] = 2;
        $this->options['SignatureMethod'] = 'HmacSHA256';
        $this->AppName = $appName;
    }


    public function addParameters($name, $values)
    {

        $this->requestParameters[$name] = $values;
    }

    public function resetParameters()
    {
        $this->requestParameters = array();
    }

    public function setParameters($config)
    {
        $this->requestParameters = $config;
    }

    /**
     * 获取相关设置信息
     *
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sends a request to Amazon via cURL
     *
     * This method will keep trying if the request was throttled.
     *
     * @return array cURL response array
     */
    protected function sendRequest($param)
    {
        $url = $this->urlBase . $this->urlbranch;
        $response = $this->fetchURL($url, $param);
        while ($response['code'] == 503 && $this->throttleStop) {
            $this->log('warning', "请求太快，将睡眠:" . $this->throttleTime . " 秒");
            sleep($this->throttleTime);
            $response = $this->sendRequest($param);
        }
        $this->rawResponses[] = $response;
        return $response;
    }

    public function checkResponse($r)
    {
        switch ($r['code']) {
            case 200:
                return $r;
                break;
            default:
                $xml = simplexml_load_string($r['body'])->Error;
                $this->log('error', 'Response Error!' . (string)$xml->Message);
                Throw new \Exception();
                break;
        }
    }


    protected function checkToken($xml)
    {
        if ($xml && $xml->NextToken && (string)$xml->HasNext != 'false' && (string)$xml->MoreResultsAvailable != 'false') {
            $this->tokenFlag = true;
            $this->options['NextToken'] = (string)$xml->NextToken;
        } else {
            unset($this->options['NextToken']);
            $this->tokenFlag = false;
        }
    }


    protected function fetchURL($url, $param)
    {
        $client = new Client();
        $response = $client->request('POST', $url . '?' . $param, ['http_errors' => false]);
        $code = $response->getStatusCode();
        $page = $response->getBody()->getContents();

        return ['code' => $code, 'body' => $page];
    }

    /**
     * Calculate String to Sign for SignatureVersion 2
     *
     * @param array $parameters request parameters
     *
     * @return String to Sign
     */
    private function _calculateStringToSignV2(array $parameters)
    {
        $data = 'POST';
        $data .= "\n";
        $endpoint = parse_url($this->urlBase . $this->urlbranch);
        $data .= $endpoint['host'];
        $data .= "\n";
        $uri = array_key_exists('path', $endpoint) ? $endpoint['path'] : null;
        if (!isset ($uri)) {
            $uri = "/";
        }
        $uriencoded = implode("/", array_map(array($this, "_urlencode"), explode("/", $uri)));
        $data .= $uriencoded;
        $data .= "\n";
        uksort($parameters, 'strcmp');
        $data .= $this->_getParametersAsString($parameters);
        if (preg_match('#Token#', $data)) {

        }
        return $data;
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    private function _getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->_urlencode($value);
        }

        return implode('&', $queryParameters);
    }

    private function _urlencode($value)
    {
        return str_replace('%7E', '~', rawurlencode($value));
    }

    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private function _sign($data, $key, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new \Exception ("Non-supported signing method specified");
        }

        return base64_encode(
            hash_hmac($hash, $data, $key, true)
        );
    }

    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * If Signature Version is 0, it signs concatenated Action and Timestamp
     *
     * If Signature Version is 1, it performs the following:
     *
     * Sorts all  parameters (including SignatureVersion and excluding Signature,
     * the value of which is being created), ignoring case.
     *
     * Iterate over the sorted list and append the parameter name (in original case)
     * and then its value. It will not URL-encode the parameter values before
     * constructing this string. There are no separators.
     *
     * If Signature Version is 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    public function _signParameters(array $parameters)
    {
        $algorithm = $this->options['SignatureMethod'];
        $stringToSign = null;
        if (2 === $this->options['SignatureVersion']) {
            $stringToSign = $this->_calculateStringToSignV2($parameters);
//            var_dump($stringToSign);
        } else {
            throw new \Exception("Invalid Signature Version specified");
        }

        return $this->_sign($stringToSign, $this->SecreKey, $algorithm);
    }

    public function genQuery()
    {
        unset($this->options['Signature']);
        $this->options['Timestamp'] = $this->genTime();
        $this->options['Signature'] = $this->_signParameters($this->options);
        return $this->_getParametersAsString($this->options);
    }

    /**
     * Generates timestamp in ISO8601 format.
     *
     * This method creates a timestamp from the provided string in ISO8601 format.
     * The string given is passed through <i>strtotime</i> before being used. The
     * value returned is actually two minutes early, to prevent it from tripping up
     * Amazon. If no time is given, the current time is used.
     *
     * @param string|int $time [optional] <p>The time to use. Since any string values are
     *                         passed through <i>strtotime</i> first, values such as "-1 hour" are fine.
     *                         Unix timestamps are also allowed. Purely numeric values are treated as unix timestamps.
     *                         Defaults to the current time.</p>
     *
     * @return string Unix timestamp of the time, minus 2 minutes.
     * @throws \InvalidArgumentException
     */
    protected function genTime($time = false)
    {
        if (!$time) {
            $time = time();
        } else if (is_numeric($time)) {
            $time = (int)$time;
        } else if (is_string($time)) {
            $time = strtotime($time);
        } else {
            throw new \InvalidArgumentException('Invalid time input given');
        }

        return date('Y-m-d\TH:i:sO', $time);

    }

    /**
     *
     * @param
     * @param $content
     */
    public function log($level, $message, $content = array())
    {
        $level = strtolower($level);
        if (!($level == 'info' || $level == 'warning' || $level == 'error')) {
            throw new \Exception('Error $level' . '=' . $level);
        }
        Log::$level('AppName = ' . $this->AppName . ":" . $message, $content);
    }
}
