<?php


namespace neoreids\GrabPay;

class GrabPayAttributes
{
    public $merchantId;
    public $partnerId;
    public $partnerSecret;
    public $clientId;
    public $clientSecret;
    public $baseHost;

    public function __construct(
        $partnerId, $partnerSecret, $clientId, $clientSecret, $merchantId, $baseHost, $redirectUri
    )
    {
        $this->partnerId = $partnerId;
        $this->partnerSecret = $partnerSecret;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->merchantId = $merchantId;
        $this->baseHost = $baseHost;
        $this->redirectUri = $redirectUri;
    }
}