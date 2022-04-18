<?php


namespace neoreids\GrabPay;


class GrabPayRedirectURL
{
    public $url;
    public $state;
    public $codeVerifier;
    public $partnerTxId;

    public function __construct($url, $state, $partnerTxId, $codeVerifier)
    {
        $this->url = $url;
        $this->state = $state;
        $this->codeVerifier = $codeVerifier;
        $this->partnerTxId = $partnerTxId;
    }
}