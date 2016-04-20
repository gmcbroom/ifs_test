<?php

namespace App\CarrierAPI;

use Illuminate\Support\Facades\Validator;

/**
 * Description of DHLWebAPI
 *
 * @author gmcbroom
 */
class Carrier {

    public $_SiteID;
    public $_Password;
    public $_Accounts;
    public $_mode;
    public $_connection;
    public $_LabelStockType;
    public $_PackageTypes;
    public $_WeightUnits;
    public $_DimensionUnits;
    public $_logo;
    public $transactionHeader = 'Test Transaction';

    function __construct($mode) {

        /*
         * *****************************************
         * Define fields for Production/ Development
         * *****************************************
         */
        $this->_mode = $mode;                                                   // Store in case need it later
        $this->initCarrier();                                                   // Set Carrier Defaults and conversion tables
    }

    function initCarrier() {
        // Carrier Specific Code
    }

    function setVersion() {
        return "IFS_API_1.1";
    }

    function logMsg($data = '', $msg = '', $type = '') { //log a transaction to the database

        /*
          if (isset($data['ShipCompanyName'])) {
          $sender = $data['ShipCompanyName'];
          } elseif (isset($data['ShipContact'])) {
          $sender = $data['ShipContact'];
          } else {
          $sender = '';
          }

          if (isset($data['CneeCompanyName'])) {
          $destination = $data['CneeCompanyName'];
          } elseif (isset($data['CneeContact'])) {
          $destination = $data['CneeContact'];
          } else {
          $destination = '';
          }

          $FX_Log = new FX_Log();
          $FX_Log->SetField('type', $type);
          $FX_Log->SetField('msg', escape_data($msg));
          $FX_Log->SetField('sender', $sender);
          $FX_Log->SetField('destination', $destination);
          $FX_Log->SetField('date', date('d-m-y'));
          $FX_Log->SetField('time', date('H:i:s'));
          $FX_Log->Insert();
         * 
         */
    }

    public function validate_shipment($shipment) {

        $errors = '';

        $shipment_validation = Validator::make($shipment, [
                    'Carrier' => 'required|in:AUTO,FEDEX',
        ]);

        if ($shipment_validation->fails()) {

            $errors = $this->build_validation_errors($shipment_validation->errors());
        }

        return $errors;
    }

    public function build_validation_errors($messages) {

        foreach ($messages->all() as $message) {
            $errors[] = $message;
        }

        return $errors;
    }

    public function generate_errors($response, $errors) {

        $response['Result'] = "ERROR";
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $response["Errors"][] = $error;
            }
        } else {
            $response["Errors"][] = $errors;
        }

        return $response;
    }

    public function getElement($shipment, $target) {

        $loc_arr = explode('.', $target);
        $data = '';

        switch (count($loc_arr)) {

            case '1':
                if (isset($shipment[$loc_arr[0]])) {
                    $data = $shipment[$loc_arr[0]];
                }
                break;

            case '2':
                if (isset($shipment[$loc_arr[0]][$loc_arr[1]])) {
                    $data = $shipment[$loc_arr[0]][$loc_arr[1]];
                }
                break;

            case '3':
                if (isset($shipment[$loc_arr[0]][$loc_arr[1]][$loc_arr[2]])) {
                    $data = $shipment[$loc_arr[0]][$loc_arr[1]][$loc_arr[2]];
                }
                break;

            case '4':
                if (isset($shipment[$loc_arr[0]][$loc_arr[1]][$loc_arr[2]][$loc_arr[3]])) {
                    $data = $shipment[$loc_arr[0]][$loc_arr[1]][$loc_arr[2]][$loc_arr[3]];
                }
                break;

            case '5':
                if (isset($shipment[$loc_arr[0]][$loc_arr[1]][$loc_arr[2]][$loc_arr[3]][$loc_arr[4]])) {
                    $data = $shipment[$loc_arr[0]][$loc_arr[1]][$loc_arr[2]][$loc_arr[3]][$loc_arr[4]];
                }
                break;
        }

        return $data;
    }

    public function getData($data, $key) {

        // Check to see if data contains a multivalue item
        $pos = strpos($key, '.*.');
        if ($pos !== false) {

            // Pull back Array
            $reply = $this->getElement($data, substr($key, 0, $pos));

            if (is_array($reply)) {
                foreach ($reply as $item) {

                    // Get only the Key value I am looking for
                    $result[] = $item[substr($key, $pos + 3)];
                }
            } else {
                $result[] = '';
            }
        } else {

            // Process as standard
            $result = $this->getElement($data, $key);
        }

        return $result;
    }

    public function get_payor_account($data, $terms) {

        switch ($terms) {
            case 'SHIPPER':
                $payor = $this->getData($data, 'Shipper.Contact.Account');
                break;

            case 'RECIPIENT':
                $payor = $this->getData($data, 'Recipient.Contact.Account');
                break;

            case 'OTHER':
                $payor = $this->getData($data, 'Other.Contact.Account');
                break;

            default:
                $payor = '';
                break;
        }

        return $payor;
    }

    public function get_payor_country($data, $terms) {

        switch ($terms) {
            case 'SHIPPER':
                $country = $this->getData($data, 'Shipper.Address.CountryCode');
                break;

            case 'RECIPIENT':
                $country = $this->getData($data, 'Recipient.Address.CountryCode');
                break;

            case 'OTHER':
                $country = $this->getData($data, 'Other.Address.CountryCode');
                break;

            default:
                $country = '';
                break;
        }

        return $country;
    }

    public function generate_error($errors, $source) {

        $response['TransactionHeader'] = $this->transactionHeader;
        $response['Result'] = 'ERROR';
        // $response['Notifications']['Source'] = $source;
        $response['Errors'] = $errors;
        // $response['TransactionDetail'] = array('CustomerTransactionId' => $this->transactionHeader);
        $response['Version'] = $this->setVersion();

        return $response;
    }

    public function generate_success($source = "API") {

        $response['TransactionHeader'] = $this->transactionHeader;
        $response['Result'] = 'SUCCESS';
        // $response['Notifications']['Source'] = $source;
        $response['Errors'] = '';
        // $response['TransactionDetail'] = array('CustomerTransactionId' => $this->transactionHeader);
        $response['Version'] = $this->setVersion();

        return $response;
    }

    /*
     * *********************************************
     * *********************************************
     * Start of Interface Calls
     * *********************************************
     * *********************************************
     */

    public function checkAddress($address) {
        // Carrier Specific Code
    }

    public function requestPickup($pickup_request) {
        // Carrier Specific Code
    }

    private function create_pickup_response($reply) {
        // Carrier Specific Code
    }

    public function cancelPickup($cancel_request) {
        // Carrier Specific Code
    }

    private function cancel_pickup_response($reply) {
        // Carrier Specific Code
    }

    public function checkAvailServices($shipment) {
        // Carrier Specific Code
    }

    private function create_availability_response($data) {
        // Carrier Specific Code
    }

    public function createShipment($shipment) {
        // Carrier Specific Code
    }

    private function create_shipment_response($reply) {
        // Carrier Specific Code
    }

}

?>
