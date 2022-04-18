<?php


namespace neoreids\GrabPay;


class GrabPayresult
{
    /**
     * @var array|mixed
     */
    public $data;
    public $statusCode;
    public $message;

    public function __construct($statusCode, $message, $data = array())
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        if (!empty($data)) {
            $this->data = $data;
        }
    }
}