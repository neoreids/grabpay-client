<?php


namespace neoreids\GrabPay;


class GrabPayRequestChargeResult
{
    public $partnerTxID;
    public $request;

    public function __construct($responseBody)
    {
        if(is_string($responseBody)) {
            $decode = json_decode($responseBody);
            $this->partnerTxID = $decode->partnerTxID;
            $this->request = $decode->request;
        }
        if (is_array($responseBody)) {
            $this->partnerTxID = $responseBody["partnerTxID"];
            $this->request = $responseBody["request"];
        }
        if (is_object($responseBody)) {
            $this->partnerTxID = $responseBody->partnerTxID;
            $this->request = $responseBody->request;
        }
    }

    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function base64URLEncode($string) {
        $base64 = base64_encode($string);
        $base64 = preg_replace("/=/", "", $base64);
        $base64 = preg_replace("/\+/", "-", $base64);
        $base64 = preg_replace("/\//", "_", $base64);
        return $base64;
    }

    protected function generateCodeVerifier($length) {
        return $this->base64URLEncode($this->generateRandomString($length));
    }

    protected function generateCodeChallenge($code) {
        return $this->base64URLEncode(hash("sha256", $code, true));
    }

    public function getAuthorizeLink($host, $clientId, $redirectUri) {
        $scope = ['openid', 'payment.one_time_charge'];
        $response_type = 'code';
        $nonce = $this->generateRandomString(16);
        $state = $this->generateRandomString(7);
        $code_challenge_method = 'S256';
        $code_verifier = $this->generateCodeVerifier(64);
        $code_challenge = $this->generateCodeChallenge($code_verifier);
        $countryCode = "SG";
        $currency = "SGD";
        $client_id = $clientId;
        $redirect_uri = $redirectUri;
        $params = [
            "client_id"=> $client_id,
            "scope"=> implode(" ", $scope),
            "response_type"=> $response_type,
            "redirect_uri"=> $redirect_uri,
            "nonce"=> $nonce,
            "state"=> $state,
            "code_challenge_method"=> $code_challenge_method,
            "code_challenge"=> $code_challenge,
            "request"=> $this->request,
            "acr_values"=> sprintf("consent_ctx:countryCode=%s,currency=%s", $countryCode, $currency)
        ];
        $url = sprintf("%s%s", $host, http_build_query($params));
        return new GrabPayRedirectURL($url, $state, $this->partnerTxID, $code_verifier);
    }
}