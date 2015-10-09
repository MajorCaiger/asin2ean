<?php

namespace Application\Service;

/**
 * Amazon
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Amazon
{
    protected $operation         = 'ItemSearch';
    protected $responseGroupName = 'ItemAttributes,Offers';
    protected $serviceHost       = 'webservices.amazon.co.uk/onca/xml';
    protected $sigVersion        = '2';
    protected $hashAlgorithm     = 'HmacSHA256';

    protected $accessKeyId;
    protected $secretAccessKey;
    protected $tag;

    public function __construct($accessKeyId, $secretAccessKey, $tag)
    {
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->tag = $tag;
    }

    /**
     * Get top sites from ATS
     */
    public function search($keywords, $type = 'ASIN')
    {
        $endpoint = "webservices.amazon.co.uk";

        $uri = "/onca/xml";

        $params = array(
            "Service" => "AWSECommerceService",
            "Operation" => "ItemLookup",
            "AWSAccessKeyId" => $this->accessKeyId,
            "AssociateTag" => $this->tag,
            "ItemId" => $keywords,
            "IdType" => $type,
            "ResponseGroup" => "Images,ItemAttributes,Offers"
        );

        if ($type !== 'ASIN') {
            $params["SearchIndex"] = "All";
        }

        if (!isset($params["Timestamp"])) {
            $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
        }

        ksort($params);

        $pairs = array();

        foreach ($params as $key => $value) {
            array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
        }

        $canonical_query_string = join("&", $pairs);

        $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

        $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $this->secretAccessKey, true));

        $request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

        return $this->respond($this->makeRequest($request_url));
    }

    protected function respond($xml)
    {
        return simplexml_load_string($xml);
    }

    /**
     * Makes an http request
     *
     * @param $url      URL to make request to
     * @return String   Result of request
     */
    protected function makeRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
