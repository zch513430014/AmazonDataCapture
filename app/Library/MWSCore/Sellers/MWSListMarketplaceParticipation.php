<?php
/**
 * Created by PhpStorm.
 * User: 51343
 * Date: 2018/5/6
 * Time: 18:39
 */

namespace App\Library\MWSCore\Sellers;

class MWSListMarketplaceParticipation extends MWSSellersCore
{
    protected $tokenFlag = false;
    protected $tokenUseFlag = false;
    protected $participationList;
    protected $marketplaceList;
    protected $indexM = 0;
    protected $indexP = 0;

    public function listMarketplaceParticipations($continue = true)
    {
        $this->prepareToken();

        $this->options['Action'] = 'ListMarketplaceParticipations';
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
//                $this->log("Recursively fetching more Participationseses");
                $this->fetchParticipationList(false);
            }
        }

        return $response;
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
            $this->options['Action'] = 'ListMarketplaceParticipationsByNextToken';
        } else {
            $this->options['Action'] = 'ListMarketplaceParticipations';
            unset($this->options['NextToken']);
            $this->marketplaceList = array();
            $this->participationList = array();
            $this->indexM = 0;
            $this->indexP = 0;
        }
    }


    /**
     * Returns the list of marketplaces.
     *
     * The returned array will contain a list of arrays, each with the following fields:
     * <ul>
     * <li><b>MarketplaceId</b></li>
     * <li><b>Name</b></li>
     * <li><b>DefaultCountryCode</b></li>
     * <li><b>DefaultCurrencyCode</b></li>
     * <li><b>DefaultLanguageCode</b></li>
     * <li><b>DomainName</b></li>
     * </ul>
     * @return array|boolean multi-dimensional array, or <b>FALSE</b> if list not filled yet
     */
    public function getMarketplaceList()
    {
        if (isset($this->marketplaceList)) {
            return $this->marketplaceList;
        } else {
            return false;
        }
    }

    /**
     * Parses XML response into two arrays.
     *
     * This is what reads the response XML and converts it into two arrays.
     * @param \SimpleXMLElement $xml <p>The XML response from Amazon.</p>
     * @return boolean <b>FALSE</b> if no XML data is found
     */
    protected function parseXML($xml)
    {
        if (!$xml) {
            return false;
        }
        $xmlP = $xml->ListParticipations;
        $xmlM = $xml->ListMarketplaces;

        foreach ($xmlP->children() as $x) {
            $this->participationList[$this->indexP]['MarketplaceId'] = (string)$x->MarketplaceId;
            $this->participationList[$this->indexP]['SellerId'] = (string)$x->SellerId;
            $this->participationList[$this->indexP]['Suspended'] = (string)$x->HasSellerSuspendedListings;
            $this->indexP++;
        }


        foreach ($xmlM->children() as $x) {
            $this->marketplaceList[$this->indexM]['MarketplaceId'] = (string)$x->MarketplaceId;
            $this->marketplaceList[$this->indexM]['Name'] = (string)$x->Name;
            $this->marketplaceList[$this->indexM]['DefaultCountryCode'] = (string)$x->DefaultCountryCode;
            $this->marketplaceList[$this->indexM]['DefaultCurrencyCode'] = (string)$x->DefaultCurrencyCode;
            $this->marketplaceList[$this->indexM]['DefaultLanguageCode'] = (string)$x->DefaultLanguageCode;
            $this->marketplaceList[$this->indexM]['DomainName'] = (string)$x->DomainName;
            $this->indexM++;
        }
    }
}