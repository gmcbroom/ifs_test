<?php

namespace App\CarrierAPI\DHL;

use SimpleXMLElement;
use DOMDocument;
use Carbon\Carbon;

/**
 * Description of DHLWebAPI
 *
 * @author gmcbroom
 */
class DHLWebAPI extends \App\CarrierAPI\Carrier {
    /*
     *  Carrier Specific Variable declarations
     */

    private $_csc;
    private $_gpc;
    private $_lpc;
    private $_svc;
    private $_terms;

    function initCarrier() {

        /*
         * *****************************************
         * Define fields for Production/ Development
         * *****************************************
         */
        $this->_SiteID = "CIMGBTest";
        $this->_Password = "DLUntOcJma";

        switch ($this->_mode) {
            case 'PRODUCTION':
                $this->_connection['url'] = "https://xmlpitest-ea.dhl.com/XMLShippingServlet";
                $this->_Accounts['Export'] = "130000279";
                $this->_Accounts['Import'] = "950804996";
                break;

            default:
                $this->_connection['url'] = "https://xmlpitest-ea.dhl.com/XMLShippingServlet";
                $this->_Accounts['Export'] = "130000279";
                $this->_Accounts['Import'] = "950804996";
                break;
        }

        /*
         * *****************************************
         * Define Carrier Specific field values
         *
         * i.e. IFS => Carrier conversion
         * *****************************************
         */

        // Label Stock Sizes
        $this->_LabelStockType['6X4'] = '6X4_PDF';
        $this->_LabelStockType['A4'] = '6X4_A4_PDF';

        // Define Package types
        $this->_PackageTypes = ['CTN' => 'YP', 'ENV' => 'DC', 'PCL' => 'PA'];

        // Define Weight Units
        $this->_WeightUnits = ['KG' => 'KG', 'LB' => 'LB'];

        // Define Short Weight Units for Send Shipment transaction
        $this->_SWeightUnits = ['KG' => 'K', 'LB' => 'L'];

        // Define DimensionUnit Units
        $this->_DimensionUnits = ['CM' => 'CM', 'IN' => 'IN'];

        // Define Short DimensionUnit Units for Send Shipment transaction
        $this->_SDimensionUnits = ['CM' => 'C', 'IN' => 'I'];

        // Carrier Service Codes
        $this->_csc['Domestic'] = 'DOM';
        $this->_csc['Domestic0900'] = 'DOK';
        $this->_csc['Domestic1200'] = 'DOT';

        // Global Product Codes
        $this->_gpc['Domestic'] = 'N';
        $this->_gpc['Domestic0900'] = 'I';
        $this->_gpc['Domestic1200'] = '1';

        // Local Product Codes
        $this->_lpc['Domestic'] = 'C';
        $this->_lpc['Domestic0900'] = 'O';
        $this->_lpc['Domestic1200'] = '1';

        // Service Codes
        $this->_svc['NC'] = 'Domestic';
        $this->_svc['IO'] = 'Domestic0900';
        $this->_svc['11'] = 'Domestic1200';

        // Terms
        $this->_terms = ['SHIPPER' => 'S', 'RECIPIENT' => 'R', 'OTHER' => 'O'];

        // Define Logo
        $this->_logo = "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";
    }

    function callCarrier($request) {

        if (!$ch = curl_init()) {
            throw new \Exception('could not initialize curl');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $this->_connection['url']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $result = curl_exec($ch);

        if (curl_error($ch)) {
            return false;
        } else {
            curl_close($ch);
        }
        return $result;
    }

    // function to convert an array to XML using SimpleXML
    function array_to_xml($array, &$xml) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $this->array_to_xml($value, $xml);
                }
            } else {
                $xml->addChild("$key", "$value");
            }
        }
    }

    function xml_to_array($root) {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = $this->xml_to_array($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = $this->xml_to_array($child);
                }
            }
        }

        return $result;
    }

    private function getReadyTime($datetime = '') {

        // Convert UK time to Pacific Time and an offset
        /*
          $format = '';
          $gmt = 'Europe/London';
          $la = 'America/Los_Angeles';
          if ($datetime == 'Y-m-d H:i:s') {
          $datetime = date($format);
          }

          $gmt_date = Carbon::createFromFormat($format, $datetime, $gmt);
          $la_date = Carbon::createFromFormat($format, $datetime, $la);
          $offset = $gmt_date->diffInHours($la_date) . ':' . $gmt_date->diffInHours($la_date);
          $readyTime = $gmt_date->setTimezone($la)->toDateTimeString();

         */
        // $response['time'] = 'PT10H21M';
        // $response['offset'] = '+01:00';
        $response['time'] = 'PT' . date('H') . 'H' . date('i') . 'M';
        $response['offset'] = '+00:00';

        return $response;
    }

    private function format_time($mytime) {

        date_default_timezone_set('Europe/London');
        $utc_offset = date('Z') / 3600;
        return substr($mytime, 2, 2) . ':' . substr($mytime, 5, 2) . ':00';
    }

    private function getGPC($data) {
        return $this->_gpc[$data['Service']];
    }

    private function getLPC($data) {
        return $this->_lpc[$data['Service']];
    }

    private function generate_headers($transactionType) {

        switch ($transactionType) {
            case 'ns1:RouteRequest':
                $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$transactionType></$transactionType>");
                $xml->addAttribute("xmlns:xmlns:ns1", "http://www.dhl.com");
                $xml->addAttribute("xmlns:xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                $xml->addAttribute("xmlns:xsi:schemaLocation", "http://www.dhl.com routing-global-req.xsd");
                $xml->addAttribute("schemaVersion", "1.0");
                break;

            case 'req:ShipmentRequest':
                $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$transactionType></$transactionType>");
                $xml->addAttribute("xmlns:xmlns:req", "http://www.dhl.com");
                $xml->addAttribute("xmlns:xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                $xml->addAttribute("xmlns:xsi:schemaLocation", "http://www.dhl.com ship-val-global-req.xsd");
                $xml->addAttribute("schemaVersion", "1.0");
                break;

            case 'GetCapability':
                $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><p:DCTRequest></p:DCTRequest>");
                $xml->addAttribute("xmlns:xmlns:p", "http://www.dhl.com");
                $xml->addAttribute("xmlns:xmlns:p1", "http://www.dhl.com/datatypes");
                $xml->addAttribute("xmlns:xmlns:p2", "http://www.dhl.com/DCTRequestdatatypes");
                $xml->addAttribute("xmlns:xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                $xml->addAttribute("xmlns:xsi:schemaLocation", "http://www.dhl.com DCT-req.xsd");
                break;

            case 'req:BookPickupRequestEA':
                $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$transactionType></$transactionType>");
                $xml->addAttribute("xmlns:xmlns:req", "http://www.dhl.com");
                $xml->addAttribute("xmlns:xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                $xml->addAttribute("xmlns:xsi:schemaLocation", "http://www.dhl.com book-pickup-req_EA.xsd");
                break;

            case 'req:CancelPickupRequestEA':
                $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$transactionType></$transactionType>");
                $xml->addAttribute("xmlns:xmlns:req", "http://www.dhl.com");
                $xml->addAttribute("xmlns:xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
                $xml->addAttribute("xmlns:xsi:schemaLocation", "http://www.dhl.com cancel-pickup-req_EA.xsd");
                break;

            default:
                break;
        }

        return $xml;
    }

    function send_message($data, $requestType) {

        libxml_use_internal_errors(true);

        try {
            $xml = $this->generate_headers($requestType);
            $this->array_to_xml($data, $xml);

            $xmlString = $this->callCarrier($xml->asXML());

            $xmlDoc = new DOMDocument();
            $xmlDoc->loadXML($xmlString);

            $response = $this->xml_to_array($xmlDoc);

            // $this->writeToLog($client);    // Write to log file
        } catch (\SoapFault $exception) {
            $errors = explode("\n", $exception->detail->desc);
            $error_code = $exception->detail->code;
            $response = $this->generate_error($errors, $error_code);
        }

        return $response;
    }

    public function setAuthentication($serviceID) {

        $request = [
            "ServiceHeader" => [
                "MessageTime" => date('c'),
                "MessageReference" => "012345678901234567890123456789",
                "SiteID" => $this->_SiteID,
                "Password" => $this->_Password
            ],
        ];

        $this->serviceID = $serviceID;

        return $request;
    }

    public function setAccountDetail($account = '604530164') {

        switch ($account) {
            case '604530164':
                $meter = '100278405';
                break;

            default:
                $meter = '';
                break;
        }
        $request = [
            'AccountNumber' => $account,
            'MeterNumber' => $meter
        ];

        return $request;
    }

    public function setVersion() {

        switch ($this->serviceID) {
            case 'aval':
                $request = [
                    'ServiceId' => $this->serviceID,
                    'Major' => '4',
                    'Intermediate' => '0',
                    'Minor' => '0'
                ];
                break;

            case 'ship':
                $request = [
                    'ServiceId' => $this->serviceID,
                    'Major' => '17',
                    'Intermediate' => '0',
                    'Minor' => '0'
                ];
                break;

            case 'pickuprequest':
                $request = [
                    'ServiceId' => $this->serviceID,
                    'Major' => '17',
                    'Intermediate' => '0',
                    'Minor' => '0'
                ];
                break;

            case 'cancelpickup':
                $request = [
                    'ServiceId' => $this->serviceID,
                    'Major' => '17',
                    'Intermediate' => '0',
                    'Minor' => '0'
                ];
                break;

            default:
                break;
        }

        return $request;
    }

    private function extract_errors($reply) {

        $error_response = '';
        $error_type = [
            "res:ErrorResponse",
            "res:ShipmentValidateErrorResponse",
            "res:PickupErrorResponse"
        ];

        foreach ($error_type as $error) {

            // Check to see if errors exist
            if (isset($reply[$error]['Response']['Status']['Condition'])) {

                $error_response = $reply[$error]['Response']['Status']['Condition'];
                $error_response = str_replace('DHL', '', $error_response);

                // Request failed with Errors
                if (isset($error_response[0])) {

                    // Multiple Errors
                    foreach ($error_response as $error) {
                        $errors[] = str_replace('XML:Error', '', $error['ConditionData']);
                    }
                } else {

                    // Single Error
                    $errors[] = str_replace('XML:Error', '', $error_response['ConditionData']);
                }
            }
        }

        return $this->generate_error($errors, 'Carrier');
    }

    /*
     * *********************************************
     * *********************************************
     * Start of Interface Calls
     * *********************************************
     * *********************************************
     */

    public function requestPickup($pickup_request) {

        $data['Request'] = $this->setAuthentication('pickuprequest');

        $data['Requestor'] = [
            "AccountType" => "D",
            "AccountNumber" => $this->_Accounts['Export'],
            "RequestorContact" => [
                "PersonName" => $pickup_request['Contact'],
                "Phone" => $pickup_request['Phone']
            ],
            "CompanyName" => $pickup_request['CompanyName']
        ];

        $data['Place'] = [
            "LocationType" => "B",
            "CompanyName" => $pickup_request['CompanyName'],
            "Address1" => $pickup_request['Address1'],
            "Address2" => $pickup_request['Address2'],
            "PackageLocation" => "Reception",
            "City" => $pickup_request['City'],
            "CountryCode" => $pickup_request['CountryCode'],
            "StateCode" => $pickup_request['County'],
            "PostalCode" => $pickup_request['PostCode']
        ];

        $data['Pickup'] = [
            "PickupDate" => $pickup_request['PickupDate'],
            "ReadyByTime" => substr($pickup_request['TimeAvailable'], 0, 5),
            "CloseTime" => substr($pickup_request['CloseTime'], 0, 5)
        ];

        $data['PickupContact'] = [
            "PersonName" => $pickup_request['Contact'],
            "Phone" => $pickup_request['Phone']
        ];

        $reply = $this->send_message($data, 'req:BookPickupRequestEA');

        if (isset($reply['res:PickupResponse']) && isset($reply['res:PickupResponse']['ConfirmationNumber'])) {

            $response = $this->create_pickup_response($reply);
        } else {

            // Request failed with Errors
            $response = $this->extract_errors($reply);
        }

        return $response;
    }

    private function create_pickup_response($reply) {

        $response = $this->generate_success();
        $response['PickupRef'] = $reply['res:PickupResponse']['ConfirmationNumber'];

        return $response;
    }

    public function cancelPickup($cancel_request) {

        /*
         * Reason Codes
          001    PACKAGE_NOT_READY
          002    RATES_TOO_HIGH
          003    TRANSIT_TIME_TOO_SLOW
          004    TAKE_TO_SERVICE_CENTER_OR_DROP_BOX
          005    COMMITMENT_TIME_NOT_MET
          006    REASON_NOT_GIVEN
          007    OTHER
          008    PICKUP_MODIFIED
         */

        $now = date('H:i:s');
        $data['Request'] = $this->setAuthentication('cancelpickup');

        $data['ConfirmationNumber'] = $cancel_request['PickupRef'];
        $data["RequestorName"] = $cancel_request['Contact'];
        $data["Reason"] = '006';
        $data["PickupDate"] = $cancel_request['PickupDate'];
        $data["CountryCode"] = $cancel_request['CountryCode'];
        $data["CancelTime"] = substr($now, 0, 5);

        $reply = $this->send_message($data, 'req:CancelPickupRequestEA');

        if (isset($reply['res:PickupResponse']) && isset($reply['res:PickupResponse']['ConfirmationNumber'])) {

            $this->create_pickup_response($response);
        } else {

            // Request failed with Errors
            $response = $this->extract_errors($error_response);
        }

        return $response;
    }

    private function cancel_pickup_response($reply) {

        // To be completed

        return $response;
    }

    public function checkAddress($address) {

        return $this->generate_success();
    }

    public function checkShipment($shipment) {

        return $this->generate_success();
    }

    public function checkAvailServices($shipment) {

        $readyTime = $this->getReadyTime();

        $this->transactionHeader = $this->getData($shipment, 'TransactionID');

        $payTerms = $this->_terms(strtoupper($this->getData($shipment, 'PayorFreight')));

        $data['Request'] = $this->setAuthentication('ship');

        $data['From'] = [
            'CountryCode' => $this->getData($shipment, 'Shipper.Address.CountryCode'),
            'Postalcode' => $this->getData($shipment, 'Shipper.Address.PostCode'),
            'City' => $this->getData($shipment, 'Shipper.Address.City'),
        ];
        $data['BkgDetails'] = [
            "PaymentCountryCode" => $this->get_payor_country($shipment, $payTerms), // "DHL Test",
            "Date" => $this->getData($shipment, 'ShipDate'), // Ship Date,
            "ReadyTime" => $readyTime['time'], // Time Available for collection
            "ReadyTimeGMTOffset" => $readyTime['offset'], // Timezone Offset
            "DimensionUnit" => $this->_DimensionUnits[$this->getData($shipment, 'DimensionUnits')],
            "WeightUnit" => $this->_WeightUnits[$this->getData($shipment, 'WeightUnits')],
            "Pieces" => [
                "Piece" => [
                    "PieceID" => $this->getData($shipment, 'PackageLineItems.0.SequenceNumber'),
                    "Height" => $this->getData($shipment, 'PackageLineItems.0.Dimensions.Height'),
                    "Depth" => $this->getData($shipment, 'PackageLineItems.0.Dimensions.Length'),
                    "Width" => $this->getData($shipment, 'PackageLineItems.0.Dimensions.Width'),
                    "Weight" => $this->getData($shipment, 'PackageLineItems.0.Weight')
                ]
            ],
            "IsDutiable" => 'N',
            "NetworkTypeCode" => 'AL',
            "QtdShp" => [
                "GlobalProductCode" => $this->getGPC($shipment),
                "LocalProductCode" => $this->getLPC($shipment)
            ]
        ];
        $data['To'] = [
            "CountryCode" => $this->getData($shipment, 'Recipient.Address.CountryCode'),
            "Postalcode" => $this->getData($shipment, 'Recipient.Address.PostCode'),
            'City' => $this->getData($shipment, 'Recipient.Address.City'),
        ];

        $msg['GetCapability'] = $data;

        $reply = $this->send_message($msg, 'GetCapability');

        if (isset($reply['res:DCTResponse']) && $reply['res:DCTResponse']["GetCapabilityResponse"]['Srvs']['Srv']['MrkSrv']['TransInd'] == 'Y') {

            $response = $this->create_availability_response($reply);
        } else {

            // Request failed with Errors
            $response = $this->extract_errors($reply);
        }

        return $response;
    }

    private function create_availability_response($data) {

        $response = $this->generate_success();
        $reply = $data["res:DCTResponse"]["GetCapabilityResponse"];

        // Add additional data to be returned
        $i = 0;
        $volWeight = 0;

        $response['PickupDate'] = $reply["BkgDetails"]["QtdShp"]['PickupDate'];
        $response['PickupTime'] = $this->format_time($reply["BkgDetails"]["QtdShp"]['BookingTime']);
        $response['TransitDays'] = $reply["BkgDetails"]["QtdShp"]['TotalTransitDays'];
        $response['DeliveryDate'] = $reply["BkgDetails"]["QtdShp"]['DeliveryDate'];
        $response['DeliveryTime'] = $this->format_time($reply["BkgDetails"]["QtdShp"]['DeliveryTime']);

        if (isset($reply["Srvs"]["Srv"][0])) {

            // Multiple Pieces
            foreach ($reply["Srvs"]["Srv"] as $service) {
                $service_code = $service['GlobalProductCode'] . $service['MrkSrv']['LocalProductCode'];
                $response['Services'][$i]['Service'] = $this->_svc[$service_code];
                $response['Services'][$i]['ServiceDesc'] = $service['MrkSrv']['ProductDesc'];
                $response['Services'][$i]['CarrierCode1'] = $service['GlobalProductCode'];
                $response['Services'][$i]['CarrierCode2'] = $service['MrkSrv']['LocalProductCode'];
                $response['Services'][$i]['Gateway'] = $service['MrkSrv']['NetworkTypeCode'];
                $i++;
                $response['ServiceCount'] = $i;
            }
        } else {

            // Single Piece
            $service_code = $reply["Srvs"]["Srv"]['GlobalProductCode'] . $reply["Srvs"]["Srv"]['MrkSrv']['LocalProductCode'];
            $response['Services'][$i]['Service'] = $this->_svc[$service_code];
            $response['Services'][$i]['ServiceDesc'] = $reply["Srvs"]["Srv"]['MrkSrv']['ProductDesc'];
            $response['Services'][$i]['CarrierCode1'] = $reply["Srvs"]["Srv"]['GlobalProductCode'];
            $response['Services'][$i]['CarrierCode2'] = $reply["Srvs"]["Srv"]['MrkSrv']['LocalProductCode'];
            $response['Services'][$i]['Gateway'] = $reply["Srvs"]["Srv"]['MrkSrv']['NetworkTypeCode'];
            $response['ServiceCount'] = 1;
        }

        return $response;
    }

    private function getShipperAccount($shipment) {

        $account = $this->getData($shipment, 'Shipper.Contact.Account');
        if ($account == '') {
            $account = $this->_Accounts['Export'];
        }

        return $account;
    }

    private function getBillingAccount($shipment) {

        $terms = $this->getData($shipment, 'PayorFreight');

        switch ($terms) {

            case 'SHIPPER':
                $account = $this->getShipperAccount($shipment);
                break;

            case 'RECIPIENT':
                $account = $this->getData($shipment, 'Recipient.Contact.Account');
                break;

            case 'OTHER':
                $account = $this->getData($shipment, 'Other.Contact.Account');
                break;

            default:
                $account = '';
                break;
        }

        return $account;
    }

    public function createShipment($shipment) {

        $this->transactionHeader = $this->getData($shipment, 'TransactionID');

        $noPieces = $this->getData($shipment, 'PackageCount');

        for ($i = 0; $i < $noPieces; $i++) {
            $pieces[$i]['Piece'] = [
                "PieceID" => $i + 1,
                "PackageType" => $this->_PackageTypes[$this->getData($shipment, 'PackageLineItems.' . $i . '.PkgType')],
                "Weight" => $this->getData($shipment, 'PackageLineItems.' . $i . '.Weight'),
                // "DimWeight" => $this->getData($shipment, 'PackageLineItems.'.$i.'.CompanyName'),
                "Width" => ceil($this->getData($shipment, 'PackageLineItems.' . $i . '.Dimensions.Width')), // Roundup to nearest integer
                "Height" => ceil($this->getData($shipment, 'PackageLineItems.' . $i . '.Dimensions.Height')), // Roundup to nearest integer
                "Depth" => ceil($this->getData($shipment, 'PackageLineItems.' . $i . '.Dimensions.Length'))                             // Roundup to nearest integer
            ];
        }

        $data['Request'] = $this->setAuthentication('ship');

        $data['RegionCode'] = "EU";
        // $data['RequestedPickupTime'] = "Y";
        $data['NewShipper'] = "N";
        $data['LanguageCode'] = "en";
        $data['PiecesEnabled'] = "Y";
        $data['Billing'] = [
            "ShipperAccountNumber" => $this->getShipperAccount($shipment),
            "ShippingPaymentType" => $this->_terms[$this->getData($shipment, 'PayorFreight')],
            "BillingAccountNumber" => $this->getBillingAccount($shipment),
            "DutyPaymentType" => $this->_terms[$this->getData($shipment, 'CustomsClearanceDetail.DutiesPayment.PaymentType')],
        ];

        $data['Consignee']["CompanyName"] = $this->getData($shipment, 'Recipient.Contact.CompanyName');
        if ($this->getData($shipment, 'Recipient.Address.AddressLine1') > "") {
            $data['Consignee'][0]["AddressLine"] = $this->getData($shipment, 'Recipient.Address.AddressLine1');
        }
        if ($this->getData($shipment, 'Recipient.Address.AddressLine2') > "") {
            $data['Consignee'][1]["AddressLine"] = $this->getData($shipment, 'Recipient.Address.AddressLine2');
        }
        if ($this->getData($shipment, 'Recipient.Address.AddressLine3') > "") {
            $data['Consignee'][2]["AddressLine"] = $this->getData($shipment, 'Recipient.Address.AddressLine3');
        }
        $data['Consignee']["City"] = $this->getData($shipment, 'Recipient.Address.City');
        $data['Consignee']["Division"] = $this->getData($shipment, 'Recipient.Contact.Division');
        $data['Consignee']["DivisionCode"] = $this->getData($shipment, 'Recipient.Contact.DivisionCode');
        $data['Consignee']["PostalCode"] = $this->getData($shipment, 'Recipient.Address.PostCode');
        $data['Consignee']["CountryCode"] = $this->getData($shipment, 'Recipient.Address.CountryCode');
        $data['Consignee']["CountryName"] = $this->getData($shipment, 'Recipient.Address.CountryName');
        $data['Consignee']["Contact"]["PersonName"] = $this->getData($shipment, 'Recipient.Contact.PersonName');
        $data['Consignee']["Contact"]["PhoneNumber"] = $this->getData($shipment, 'Recipient.Contact.PhoneNumber');

        $data['Dutiable'] = [
            "DeclaredValue" => $this->getData($shipment, 'CustomsClearanceDetail.CustomsValue.Amount'),
            "DeclaredCurrency" => $this->getData($shipment, 'CustomsClearanceDetail.CustomsValue.Currency')
        ];
        $data['Reference'] = [
            "ReferenceID" => $this->getData($shipment, 'Reference')
        ];
        $data['ShipmentDetails'] = [
            "NumberOfPieces" => $noPieces,
            "Pieces" => $pieces,
            "Weight" => $this->getData($shipment, 'TotalWeight'),
            "WeightUnit" => $this->_SWeightUnits[$this->getData($shipment, 'WeightUnits')],
            "GlobalProductCode" => $this->getGPC($shipment),
            "LocalProductCode" => $this->getLPC($shipment),
            "Date" => $this->getData($shipment, 'ShipDate'),
            "Contents" => $this->getData($shipment, 'Contents'),
            // "DoorTo" => $this->getData($shipment, 'Shipper.Contact.CompanyName'),
            "DimensionUnit" => $this->_SDimensionUnits[$this->getData($shipment, 'DimensionUnits')],
            // "InsuredAmount" => $this->getData($shipment, 'Shipper.Contact.CompanyName'),
            // "PackageType" => $this->getData($shipment, 'PackageLineItems.0.PkgType'),
            "IsDutiable" => $this->getData($shipment, 'IsDutiable'),
            "CurrencyCode" => $this->getData($shipment, 'CurrencyCode')
        ];

        $data['Shipper']["ShipperID"] = $this->_Accounts['Export'];
        $data['Shipper']["CompanyName"] = $this->getData($shipment, 'Shipper.Contact.CompanyName');
        if ($this->getData($shipment, 'Recipient.Address.AddressLine1') > "") {
            $data['Shipper'][0]["AddressLine"] = $this->getData($shipment, 'Shipper.Address.AddressLine1');
        }
        if ($this->getData($shipment, 'Recipient.Address.AddressLine2') > "") {
            $data['Shipper'][1]["AddressLine"] = $this->getData($shipment, 'Shipper.Address.AddressLine2');
        }
        if ($this->getData($shipment, 'Recipient.Address.AddressLine3') > "") {
            $data['Shipper'][2]["AddressLine"] = $this->getData($shipment, 'Shipper.Address.AddressLine3');
        }
        $data['Shipper']["City"] = $this->getData($shipment, 'Shipper.Address.City');
        $data['Shipper']["Division"] = $this->getData($shipment, 'Shipper.Contact.Division');
        $data['Shipper']["DivisionCode"] = $this->getData($shipment, 'Shipper.Contact.DivisionCode');
        $data['Shipper']["PostalCode"] = $this->getData($shipment, 'Shipper.Address.PostCode');
        $data['Shipper']["CountryCode"] = $this->getData($shipment, 'Shipper.Address.CountryCode');
        $data['Shipper']["CountryName"] = $this->getData($shipment, 'Shipper.Address.CountryName');
        $data['Shipper']["Contact"]["PersonName"] = $this->getData($shipment, 'Shipper.Contact.PersonName');
        $data['Shipper']["Contact"]["PhoneNumber"] = $this->getData($shipment, 'Shipper.Contact.PhoneNumber');

        $options = $this->getData($shipment, 'SpecialServices');

        if ($options == '') {

            // Set any default Service Codes
            $data[$i]['SpecialService']['SpecialServiceType'] = 'A';
            $data[$i]['SpecialService']['SpecialServiceType'] = 'I';
        } else {

            foreach ($options as $option) {
                
                // Take appropriate action for each special service code
                switch ($option) {

                    case 'OPTIONVALUE':
                        // Service Specific action
                        break;

                    default:
                        break;
                }
            }
        }
        $data['LabelImageFormat'] = $this->getData($shipment, 'LabelSpecification.ImageType');
        $data['Label'] = [
            "HideAccount" => 'Y',
            "LabelTemplate" => $this->_LabelStockType[$this->getData($shipment, 'LabelSpecification.LabelStockType')],
            "Logo" => 'Y',
            "CustomerLogo" => [
                "LogoImage" => $this->_logo,
                "LogoImageFormat" => 'PNG'
            ],
            "Resolution" => '300'
        ];

        $reply = $this->send_message($data, 'req:ShipmentRequest');

        if (isset($reply['res:ShipmentResponse']) && $reply['res:ShipmentResponse']['Note']['ActionNote'] == 'Success') {

            // Request succesful - Prepare Response
            $response = $this->create_shipment_response($reply['res:ShipmentResponse']);
        } else {

            $response = $this->extract_errors($reply);
        }
        return $response;
    }

    private function create_shipment_response($reply) {

        $response = $this->generate_success();

        // Add additional data to be returned
        $i = 0;
        $volWeight = 0;

        $response['AirwayBill'] = $reply['AirwayBillNumber'];

        if (isset($reply['Pieces']['Piece'][0])) {

            // Multiple Pieces
            foreach ($reply['Pieces']['Piece'] as $piece) {

                $response['PackageLineItems'][$i]['SequenceNumber'] = $reply['Pieces']['Piece'][$i]['PieceNumber'];
                $response['PackageLineItems'][$i]['PackageNo'] = $reply['Pieces']['Piece'][$i]['LicensePlate'];
                $response['PackageLineItems'][$i]['Barcode'] = $reply['Pieces']['Piece'][$i]['LicensePlate'];
                $i++;
            }
        } else {

            // Single Piece
            $response['PackageLineItems'][$i]['SequenceNumber'] = $reply['Pieces']['Piece']['PieceNumber'];
            $response['PackageLineItems'][$i]['PackageNo'] = $reply['Pieces']['Piece']['LicensePlate'];
            $response['PackageLineItems'][$i]['Barcode'] = $reply['Pieces']['Piece']['LicensePlate'];
        }

        $response['LabelFormat'] = $reply['LabelImage']['OutputFormat'];
        $response['LabelBase64'] = $reply['LabelImage']['OutputImage'];

        return $response;
    }

}

?>
