<?php

namespace App\CarrierAPI\EasyPost;

use SoapClient;
use \EasyPost\EasyPost;

// Copyright 2009, FedEx Corporation. All rights reserved.

class EasyPostAPI {

    private $api_key = "JhU5G2oFG0I6XsZBD9ZaCg";

    function __construct() {

    }

    private function generate_headers($response, $transactionType) {
        $data['TransactionType'] = $transactionType;
        $data['Result'] = $response->HighestSeverity;
        $data['Message'] = $response->Notifications->Message;
        $data['Timestamp'] = date('Y-m-d h:i:s');
        $data['Version'] = '1.0';

        return $data;
    }

    function send_message($request) {

    }

    public function setAuthentication() {

    }

    public function setAccountDetail($account = '604530164') {

    }

    function setVersion() {

    }

    /*
     * *****************************************
     * Convert from IFS Format to Carrier Format
     * *****************************************
     */

    public function setAddress($address = '') {

    }

    public function getAddress($response) {

    }

    public function checkAddress($address) {

    }

    function generate_error($exception, $client) {

        $response = '<?xml version="1.0" encoding="UTF-8"?>';
        $response .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">';
        $response .= '<SOAP-ENV:Header xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"/>';
        $response .= '<soapenv:Body>';
        $response .= '<v4:AddressValidationReply xmlns:v4="http://fedex.com/ws/addressvalidation/v4">';
        $response .= '<v4:HighestSeverity>ERROR</v4:HighestSeverity>';
        $response .= '<v4:Notifications>';
        $response .= '<v4:Severity>ERROR</v4:Severity>';
        $response .= '<v4:Source>wsi</v4:Source>';
        $response .= '<v4:Code>' . $exception->faultcode . '</v4:Code>';
        $response .= '<v4:Message>' . $exception->faultstring . '</v4:Message>';
        $response .= '</v4:Notifications>';
        $response .= '</v4:AddressValidationReply>';
        $response .= '</soapenv:Body>';
        $response .= '</soapenv:Envelope>';

        return $response;
    }

    /**
     * SOAP request/response logging to a file
     */
    function writeToLog($client) {

    }

    function setEndpoint($var) {

    }

    function trackDetails($details, $spacer) {

    }

    public function create_tracker($carrier, $consignmentno) {

        EasyPost::setApiKey($this->api_key);

        $tracker = \EasyPost\Tracker::create(array(
                    "tracking_code" => "9400110898825022579493",
                    "carrier" => "USPS"
        ));

        dd($tracker);

    }

}

?>