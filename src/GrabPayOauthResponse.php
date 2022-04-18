<?php


namespace neoreids\GrabPay;


use DateTime;
use DateTimeZone;

class GrabPayOauthResponse
{
    public $access_token;
    public $token_type;
    public $expires_in;
    public $id_token;
    public $payload ;
    public $message;
    public $date;

    public function __construct( $responseBody, $statusCode = 200, $payload = null)
    {
        $this->statusCode = $statusCode;
        if(is_object($responseBody)) {
            $this->access_token = $responseBody->access_token;
            $this->token_type = $responseBody->token_type;
            $this->expires_in = $responseBody->expires_in;
            $this->id_token = $responseBody->id_token;
        }
        if(is_string($responseBody)) {
            $this->message = $responseBody;
        }
        $this->payload = $payload;
    }

    protected function base64URLEncode($string) {
        $base64 = preg_replace("/=/", "", $string);
        $base64 = preg_replace("/\+/", "-", $base64);
        $base64 = preg_replace("/\//", "_", $base64);
        return $base64;
    }

    public function generatePOPSignature($clientSecret)
    {
        $now = (new DateTime("now", new DateTimeZone('UTC')));
        $this->date = $now->format("D, d M Y H:i:s");
        $timeUnix = $now->getTimestamp();
        $message = sprintf("%s%s", (string) $timeUnix, $this->access_token);
        $signature = base64_encode(hash_hmac("SHA256", $message, $clientSecret, true));
        $sub = $this->base64URLEncode($signature);
        $payload = [
            "time_since_epoch"=> $timeUnix,
            "sig"=> $sub
        ];
        $payloadBytes = (string) json_encode($payload);
        return $this->base64URLEncode(base64_encode($payloadBytes));
    }
}