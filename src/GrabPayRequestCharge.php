<?php


namespace neoreids\GrabPay;


use DateTime;
use DateTimeZone;

class GrabPayRequestCharge
{
    public $orderId;
    public $amount;
    public $currency;
    public $description;
    public $url = "grabpay/partner/v2/charge/init";

    public function __construct(
        $orderId, $amount, $curreny, $description
    )
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->currency = $curreny;
        $this->description = $description;
    }

    public function buildPayloadCharge($merchantId, $partnerId, $partnerSecret)
    {
        $now = (new DateTime("now", new DateTimeZone('UTC')))->format("D, d M Y H:i:s");
        $now = sprintf("%s GMT", $now);
        $arrayPayload = [
            "partnerGroupTxID"=>$this->orderId,
            "partnerTxID"=>$this->orderId,
            "currency"=>$this->currency,
            "amount"=>$this->amount,
            "description"=>$this->description,
            "merchantID"=>$merchantId
        ];
        $payloadJsonString = json_encode($arrayPayload);
        $shaltedPayload = base64_encode(hash("sha256", $payloadJsonString, true));
        $arrayPayloadHmac = [
            "POST",
            "application/json",
            $now,
            sprintf("/%s", $this->url),
            $shaltedPayload
        ];
        $concatStringArray = implode("\n", $arrayPayloadHmac);
        $stringPayloadHmac = implode("", [$concatStringArray, "\n"]);
        $signature = base64_encode(hash_hmac("SHA256", $stringPayloadHmac, $partnerSecret, true));

        return new GrabPayPayloadCharge($now, sprintf("%s:%s", $partnerId, $signature), $arrayPayload);
    }
}