<?php


namespace neoreids\GrabPay;


class GrabPayPayloadCharge
{
    public $date;
    public $hmac;
    public $requestBody;

    public function __construct($date, $hmac, $requestBody)
    {
        $this->date = $date;
        $this->hmac = $hmac;
        $this->requestBody = $requestBody;
    }
}