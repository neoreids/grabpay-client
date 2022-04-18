<?php
namespace neoreids\GrabPay;

use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class GrabPay
{
    /**
     * @var GrabPayAttributes
     */
    private $attributes;
    /**
     * @var Client
     */
    private $httpClient;
    /**
     * @var array
     */
    public $payload;

    public function __construct(GrabPayAttributes $attributes)
    {
        $this->attributes = $attributes;
        $this->httpClient = new Client([
            "base_uri"=>$this->attributes->baseHost,
            "headers"=>[
                "Content-Type"=>"application/json"
            ]
        ]);
    }

    public function getServiceDiscoveryAPI()
    {
        $url = "grabid/v1/oauth2/.well-known/openid-configuration";
        $response = $this->httpClient->get($url);
        return json_decode($response->getBody());
    }

    public function InitCharge(GrabPayRequestCharge $request)
    {
        $serviceDiscovery = $this->getServiceDiscoveryAPI();
        $hostWebUrl = sprintf("%s?", $serviceDiscovery->authorization_endpoint);
        $this->payload = $request->buildPayloadCharge($this->attributes->merchantId,
            $this->attributes->partnerId,
            $this->attributes->partnerSecret);
        $headers = [
            "Date"=> $this->payload->date,
            "Authorization"=> $this->payload->hmac,
        ];

        try {
            $response = $this->httpClient->post($request->url, [
                "headers"=> $headers,
                "json"=>$this->payload->requestBody
            ]);
            $bodyResponse = $response->getBody();
            $resultBodyResponse = new GrabPayRequestChargeResult(json_decode($bodyResponse));
            return $resultBodyResponse->getAuthorizeLink($hostWebUrl,
                $this->attributes->clientId,
                $this->attributes->redirectUri);
        } catch (GuzzleException $e) {
            return new GrabPayresult($e->getCode(), $e->getMessage());
        }
    }

    public function getOauthToken($oauthEndpoint, $codeRedirectUri, $codeVerifier)
    {
        $requestBody = [
            "client_id"=> $this->attributes->clientId,
            "code"=>$codeRedirectUri,
            "redirect_uri"=>$this->attributes->redirectUri,
            "client_secret"=>$this->attributes->clientSecret,
            "code_verifier"=>$codeVerifier,
            "grant_type"=> "authorization_code"
        ];
        try {
            $response = $this->httpClient->post($oauthEndpoint, [
                "json"=>$requestBody
            ]);
            $oauth_response = new GrabPayOauthResponse(json_decode($response->getBody()), 200, $requestBody);
            return $oauth_response;
        } catch (GuzzleException $exception) {
            $oauth_response = new GrabPayOauthResponse($exception->getMessage(), $exception->getCode(), $requestBody);
            return $oauth_response;
        }
    }

    public function completePayment($codeRedirectUri, $codeVerifier, $partnerTxID)
    {
        $serviceDiscovery = $this->getServiceDiscoveryAPI();
        $oauth = $this->getOauthToken($serviceDiscovery->token_endpoint, $codeRedirectUri, $codeVerifier);
        if ($oauth->statusCode != 200) {
            return new GrabPayresult($oauth->statusCode, $oauth->message);
        }

        $now = (new DateTime("now", new DateTimeZone('UTC')))->format("D, d M Y H:i:s");
        $headers = [
            "Authorization" => sprintf("%s %s", $oauth->token_type, $oauth->access_token),
            "X-GID-AUX-POP" => $oauth->generatePOPSignature($this->attributes->clientSecret),
            "Date" => sprintf("%s GMT", $now)
        ];
        try {
            $response = $this->httpClient->post("grabpay/partner/v2/charge/complete", [
                "headers"=>$headers,
                "json"=> [
                    "partnerTxID"=> $partnerTxID
                ]
            ]);
             return new GrabPayresult($response->getStatusCode(), "ok", json_decode($response->getBody()));
        } catch (GuzzleException $exception) {
            return new GrabPayresult($exception->getCode(), $exception->getMessage(), $headers);
        }
    }
}