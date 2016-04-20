<?php

namespace App\Http\Controllers;

use App\Carrier;
use App\Shipment;
use App\Scan;
use App\Log;
use Input;
use \fpdi\FPDI;
use TCPDF;
use Auth;
use EasyPost;
use App\Http\Controllers\Controller;
use App\CarrierAPI\CarrierAPI;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class APIController extends \App\CarrierAPI\BaseApiController {

    private $transactionHeader;

    public function __construct() {

        // generate a psuedo random id for transaction header
        $this->transactionHeader = bin2hex(openssl_random_pseudo_bytes(14));
    }

    private function getUserID($data) {

        if (Auth::check()) {
            echo "User is logged on";
        } else {
            echo "User is not logged on";
        }
        exit();

        $user_id = '';
        $user = null;

        if (isset($data['UserID']) && $data['UserID'] > '') {

            // UserID provided so try to use it
            if (Auth::attempt(['email' => $data['UserID'], 'password' => $data['Password']])) {

                // Authentication passed - get user
                $user = Auth::user();
            }
        } else {

            // Try authenticating using Token
            $user = Auth::guard('api')->user();
        }

        if (Auth::check()) {

            // The user is logged in...
            return $user_id;
        } else {
            return 'UnAuthorized';
        }
    }

    private function decodeInput($mode) {

        switch ($mode) {
            case 'JSON':
                $input = Input::json()->all();
                $input['Data']['UserID'] = $this->getUserID($input['Auth']);
                break;

            case 'XML':
                $input = Input::xml()->all();                                   // Check function exists
                break;

            default:
                $input = '';
                break;
        }

        return (array) $input;                                                  // An array containing data
    }

    private function format_response($data, $format) {

        switch ($format) {
            case "XML":
                $response = $this->create_xml($data);
                break;

            case "JSON":
                $response = json_encode($data);
                break;
            default:
                break;
        }

        return $response;
    }

    private function generate_errors($errors) {

        $response['HighestSeverity'] = 'ERROR';
        $response['Notifications']['Severity'] = 'ERROR';
        $response['Notifications']['Source'] = 'API';
        $response['Notifications']['Faultcode'] = 'DATA ERROR';
        $response['Notifications']['Message'] = 'Fatal Error';
        $response['Notifications']['Errors'] = $errors;
        $response['TransactionDetail'] = array('CustomerTransactionId' => $this->transactionHeader);
        $response['Version'] = $this->setVersion();

        return $response;
    }

    private function address_validation($address) {

        $errors = '';

        $address_validation = Validator::make($address, [
                    'AddressLine1' => 'required|string|max:45',
                    'AddressLine2' => 'string|max:45',
                    'AddressLine3' => 'string|max:45',
                    'PostCode' => 'string|max:12',
                    'City' => 'required|string|max:45',
                    'County' => 'required|string|max:45',
                    'CountryCode' => 'required|string|max:2'
        ]);

        if ($address_validation->fails()) {

            $messages = $address_validation->errors();
            foreach ($messages->all() as $message) {
                $errors = $message;
            }
        }

        return $errors;
    }

    /**
     * APIs receive request as either JSON or XML
     *
     * They extract the data and send to carrierAPI
     * as an array.
     *
     * The response (array) is then converted to
     * JSON or XML and returned.
     *
     * @return JSON or XML
     */
    public function checkAddress($mode) {

        $input = $this->decodeInput($mode);                                     // Decodes data and inserts UserID

        if ($input['UserID'] == 'UnAuthorized') {

            $response = $this->respondUnAuthorized();
        } else {

            $errors = $this->address_validation($input['Data'], 'checkAddress'); // Generic Validation

            if ($errors == '') {

                $response = new CarrierAPI;
                $response = $this->respondSuccess($carrierAPI->checkAddress($input['Data']));          // Returns an array
            } else {

                $response = $this->respondInvalid($errors);
            }
        }

        return $this->format_response($response, $mode);
    }

    public function checkShipment($mode) {

        $input = $this->decodeInput($mode);                                     // Decodes data and inserts UserID

        $errors = $this->validation($input['Data'], 'checkShipment');           // Generic Validation

        if ($errors == '') {

            // $carrierAPI = new CarrierAPI;
            $response = $carrierAPI->checkShipment($input['Data']);
        } else {
            $response = $this->respondInvalid($errors);
        }

        /*
         * return Response::json(['data' => $data], 200);
         */

        return Response::json($response, 200);
    }

    public function createShipment($mode) {

        $input = $this->decodeInput($mode);                                     // Decodes data and inserts UserID

        if ($input['Data']['UserID'] == 'UnAuthorized') {

            // Authentication Failed
            $response = $this->respondUnauthorized();
        } else {

            $carrierAPI = new CarrierAPI;
            $response = $this->carrierResponse($carrierAPI->createShipment($input['Data']));
        }

        return $response;
    }

    public function cancelShipment($mode) {

        $input = $this->decodeInput($mode);                                     // Decodes data and inserts UserID

        $errors = $this->validation($input, 'cancelShipment');                  // Generic Validation

        if ($errors == '') {
            $carrierAPI = new CarrierAPI;
            $response = $carrierAPI->cancelShipment($input['Data']);
        } else {
            $response = $this->respondInvalid($errors);
        }

        return $response;
    }

    public function requestPickup($mode) {

        $input = $this->decodeInput($mode);                                     // Decodes data and inserts UserID

        $availServices['Result'] = 'SUCCESS';

        // Generic Validation
        $errors = $this->shipment_validation($input['Data']);
        if ($errors == '') {

            // No errors so create Carrier and check Availability
            $carrierAPI = new CarrierAPI;

            //$availServices = $carrierAPI->checkAvailServices($input['Data']);
            if ($availServices['Result'] == 'SUCCESS') {

                // Service available so create shipment or return service supplier Error
                $response = $carrierAPI->createShipment($input['Data']);
            } else {

                // Return Errors
                $errors = $availServices['errors'];
                $response = $this->respondInvalid($errors);
            }
        } else {
            $response = $this->respondInvalid($errors);
        }

        return $response;
    }

    public function testPDF() {

        $labels = [
            654896494959,
            654896494970,
            654896494992
        ];

        $label_loc = "http://192.168.10.34/";

        // Parameters (Orientation, Size_Units, Page_Size, Custom Page)
        $pdf = new TCPDF('p', 'in', array(6, 4), true, 'UTF-8', false);

        // $pdf = new TCPDF();
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        foreach ($labels as $label) {
            $pdf->AddPage();
            $pdf->Image($label_loc . $label . '.PNG', $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array()
            );
        }

        $response = $pdf->output($labels[0], 'S');

        return response($response)
                        ->header('Content-Type', 'application/pdf');
    }

    /*
     * ***************************************
     *
     * Receive tracking events from EasyPost
     *
     * ***************************************
     */

    public function trackEasypost() {

        $test = false;
        $carrier_id = '';

        // Read Tracking Info into array
        if ($test) {
            $json = '{"mode":"test","description":"tracker.updated","previous_attributes":{"status":"unknown"},"pending_urls":["http:\/\/dlogistics.arrived.club\/api\/v1\/tracker\/easypost"],"completed_urls":[],"created_at":"2016-03-26T20:27:41Z","updated_at":"2016-03-26T20:27:41Z","result":{"id":"trk_cc81d2cf9efc41b095cdbccb9a119013","object":"Tracker","mode":"test","tracking_code":"EZ2000000002","status":"in_transit","created_at":"2016-03-26T20:26:41Z","updated_at":"2016-03-26T20:26:41Z","signed_by":null,"weight":null,"est_delivery_date":"2014-08-27T00:00:00Z","shipment_id":null,"carrier":"USPS","tracking_details":[{"object":"TrackingDetail","message":"August 21 Pre-Shipment Info Sent to USPS","status":"pre_transit","datetime":"2016-02-26T20:26:41Z","source":"USPS","tracking_location":{"object":"TrackingLocation","city":null,"state":null,"country":null,"zip":null}},{"object":"TrackingDetail","message":"August 21 12:37 pm Shipping Label Created in HOUSTON, TX","status":"pre_transit","datetime":"2016-02-27T09:03:41Z","source":"USPS","tracking_location":{"object":"TrackingLocation","city":"HOUSTON","state":"TX","country":null,"zip":"77063"}},{"object":"TrackingDetail","message":"August 21 10:42 pm Arrived at USPS Origin Facility in NORTH HOUSTON, TX","status":"in_transit","datetime":"2016-02-27T19:08:41Z","source":"USPS","tracking_location":{"object":"TrackingLocation","city":"NORTH HOUSTON","state":"TX","country":null,"zip":"77315"}},{"object":"TrackingDetail","message":"August 23 12:18 am Arrived at USPS Facility in COLUMBIA, SC","status":"in_transit","datetime":"2016-02-28T20:44:41Z","source":"USPS","tracking_location":{"object":"TrackingLocation","city":"COLUMBIA","state":"SC","country":null,"zip":"29201"}},{"object":"TrackingDetail","message":"August 23 3:09 am Arrived at Post Office in CHARLESTON, SC","status":"in_transit","datetime":"2016-02-28T23:35:41Z","source":"USPS","tracking_location":{"object":"TrackingLocation","city":"CHARLESTON","state":"SC","country":null,"zip":"29407"}},{"object":"TrackingDetail","message":"August 23 8:49 am Sorting Complete in CHARLESTON, SC","status":"in_transit","datetime":"2016-02-29T05:15:41Z","source":"USPS","tracking_location":{"object":"TrackingLocation","city":"CHARLESTON","state":"SC","country":null,"zip":"29407"}}],"carrier_detail":null,"fees":[{"object":"Fee","type":"TrackerFee","amount":"0.00000","charged":false,"refunded":false}]},"id":"evt_7376ab20092d457c969c88db4d18404d","object":"Event"}';
            $data = json_decode($json, true);
        } else {
            $data = Input::json()->all();
        }

        $input = $data['result'];

        // Find Carrier id
        $carrier = Carrier::where('carrier_code', "=", $input['carrier'])->first();
        if (!is_null($carrier)) {
            $carrier_id = $carrier->id;
        }

        // Find Shipment
        if ($carrier_id > '') {
            $shipment = Shipment::where('consignmentno', '=', $input['tracking_code'])
                    ->where('carrier_id', '=', $carrier_id)
                    ->first();

            // UpdateShipment Record if found
            if (!is_null($shipment)) {

                $shipment_id = $shipment->id;
                $shipment->status = $input['status'];
                $shipment->signed_by = $input['signed_by'];
                $shipment->scanned_weight = $input['weight'];
                $shipment->save();

                // Update Tracking Records
                $item_id = 1;

                foreach ($input['tracking_details'] as $track) {
                    $scan = Scan::firstOrNew([
                                'shipment_id' => $shipment_id,
                                'item_id' => $item_id
                    ]);

                    $scan->message = $track['message'];
                    $scan->status = $track['status'];
                    $scan->datetime = $track['datetime'];
                    $scan->source = $track['source'];
                    $scan->town = $track['tracking_location']['city'];
                    $scan->county = $track['tracking_location']['state'];
                    $scan->country = $track['tracking_location']['country'];
                    $scan->postcode = $track['tracking_location']['zip'];
                    $scan->save();
                    $item_id++;
                }
            } else {
                // Write to log file
                $log = new Log;
                $log->log = json_encode($input);
                $log->save();
            }
        } else {
            // Write to log file
            $log = new Log;
            $log->log = json_encode($input);
            $log->save();
        }
    }

    public function trackTest() {

        /*

          URL - http://dlogistics.club/api/v1/tracker/easypost
          ID  - hook_648676ff6178480aaab4d46e67f98a03

          Tracking_code Status
          EZ1000000001 	pre_transit
          EZ2000000002 	in_transit
          EZ3000000003 	out_for_delivery
          EZ4000000004 	delivered
          EZ5000000005 	return_to_sender
          EZ6000000006 	failure
          EZ7000000007 	unknown
         */

        \EasyPost\EasyPost::setApiKey("JhU5G2oFG0I6XsZBD9ZaCg");

        $tracker = \EasyPost\Tracker::create(array(
                    "tracking_code" => "EZ3000000003",
                    "carrier" => "USPS"
        ));

        $trackers = \EasyPost\Tracker::all(array(
                    "page_size" => 2,
                    "start_datetime" => "2016-01-02T08:50:00Z"
        ));

        dd('Tracker Response : ' . $tracker);
    }

}
