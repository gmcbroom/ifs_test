<?php

namespace App\CarrierAPI;

use Auth;
use App\Carrier;
use App\Pickup;
use App\CarrierAPI\Fedex\FedexAPI;
use App\CarrierAPI\DHL\DHLWebAPI;
use Illuminate\Support\Facades\DB;
use XmlWriter;
use Illuminate\Support\Facades\Validator;

/**
 * Description of CarrierWebServices
 *
 * @author gmcbroom
 */
class CarrierAPI {

    private $carrier;
    private $vol_divisor = 5000;

    /*
     * ************************************
     * Validator Functions
     * ************************************
     */

    private function details_required($type) {

        switch ($type) {
            case 'Shipper':
            case 'Recipient':
                $required = 'required|';
                break;

            default:
                $required = '';
                break;
        }

        return $required;
    }

    private function add_contact_rules($contact_type = 'Recipient', $rules = '') {

        $required = $this->details_required($contact_type);

        $rules["$contact_type.Contact.PersonName"] = $required . "string";
        $rules["$contact_type.Contact.CompanyName"] = "string";
        $rules["$contact_type.Contact.PhoneNumber"] = $required . "string";
        $rules["$contact_type.Contact.Email"] = $required . "email";
        $rules["$contact_type.Contact.Account"] = "string|max:12";
        $rules["$contact_type.Contact.ID"] = "string";

        return $rules;
    }

    private function add_address_rules($address_type = 'Recipient', $rules = '') {

        $required = $this->details_required($address_type);

        $rules["$address_type.Address.AddressLine1"] = $required . "string";
        $rules["$address_type.Address.AddressLine2"] = "string";
        $rules["$address_type.Address.AddressLine3"] = "string";
        $rules["$address_type.Address.City"] = $required . "string";
        $rules["$address_type.Address.County"] = $required . "string";
        $rules["$address_type.Address.PostCode"] = "string";
        $rules["$address_type.Address.CountryCode"] = $required . "string|size:2";

        return $rules;
    }

    private function add_shipment_rules($shipment) {

        $rules['UserID'] = 'required|integer';
        $rules['TransactionID'] = 'string';
        $rules['ShipDate'] = 'required|date_format:Y-m-d|after:yesterday';
        $rules['Carrier'] = 'required|in:AUTO,DHL,FEDEX,UPS,IFS';
        $rules['Pickup'] = 'required|in:N,Y';
        $rules['Service'] = 'required|in:UK48,NI48,IE48,IP,IPF,IE,Domestic,Domestic0900,Domestic1200,ExpressDOC,ExpressEU,Express';
        $rules['PackagingType'] = 'required|in:OWN,ENV,PAK';
        $rules['PackageCount'] = 'required|integer|min:1|max:99';
        $rules['TotalWeight'] = 'required|numeric|min:0.1|max:19999';
        $rules['TotalVolWeight'] = 'required|numeric|min:0.1|max:19999';
        $rules['WeightUnits'] = 'required|in:KG,LB';
        $rules['DimensionUnits'] = 'required|in:CM,IN';
        $rules['CountryOfDestination'] = 'required|string|size:2';
        $rules['Reference'] = 'required|string';
        $rules['Contents'] = 'required|string';
        $rules['Instructions'] = 'required|string';
        $rules['IsDutiable'] = 'required|in:Y,N';
        $rules['InsuredAmount'] = 'required|numeric';
        $rules['CurrencyCode'] = 'required|string|size:3';
        $rules['PayorFreight'] = 'required|in:SHIPPER,RECIPIENT,OTHER';
        $rules['PayorDuty'] = 'required|in:SHIPPER,RECIPIENT,OTHER';
        $rules = $this->add_contact_rules('Shipper', $rules);
        $rules = $this->add_address_rules('Shipper', $rules);
        $rules = $this->add_contact_rules('Recipient', $rules);
        $rules = $this->add_address_rules('Recipient', $rules);

        if (isset($shipment['Other'])) {
            $rules = $this->add_contact_rules('Other', $rules);
            $rules = $this->add_address_rules('Other', $rules);
        }

        if (isset($shipment['BrokerSelect'])) {
            $rules['BrokerSelect'] = 'in:N,Y';
            $rules = $this->add_contact_rules('Broker', $rules);
            $rules = $this->add_address_rules('Broker', $rules);
        }
        $rules['PackageDetail'] = 'in:INDIVIDUAL_PACKAGES';
        $rules['Packages.Package.*.SequenceNumber'] = 'required|integer|min:1|max:99';
        $rules['Packages.Package.*.PkgType'] = 'required|in:CTN,PAL,PAK,BOX,ROLL,TUBE,BOX15,BOX25,ENV,PCL';
        $rules['Packages.Package.*.Weight'] = 'required|integer|min:1|max:9999';
        $rules['Packages.Package.*.Length'] = 'required|integer|min:1|max:999';
        $rules['Packages.Package.*.Width'] = 'required|integer|min:1|max:999';
        $rules['Packages.Package.*.Height'] = 'required|integer|min:1|max:999';

        $rules['SpecialServices'] = 'sometimes|in:HOLD,SATDELIV';

        $rules['AlertMsg'] = 'string';
        $rules['Alerts.Alert.*.EmailAddress'] = 'required_with:AlertMsg|email';
        $rules['Alerts.Alert.*.ShipAlert'] = 'required_with:AlertMsg|in:Y,N';
        $rules['Alerts.Alert.*.PodAlert'] = 'required_with:AlertMsg|in:Y,N';
        $rules['Alerts.Alert.*.Language'] = 'required_with:AlertMsg|string|size:2';

        $rules['Documents.DocFlag'] = 'required|in:Y,N';
        $rules['Documents.Content'] = 'required_if:Documents.DocFlag,Y|string';

        $rules['DGoods.DgFlag'] = 'sometimes|in:Y,N';
        $rules['DGoods.Class'] = 'required_if:DGoods.DgFlag,Y|numeric|min:1|max:9.99';
        $rules['DGoods.Excep_Qty'] = 'required_if:DGoods.DgFlag,Y|in:Y,N,A,I';
        $rules['DGoods.Comm_Cnt'] = 'required_if:DGoods.DgFlag,Y|integer';
        $rules['DGoods.Name_Sig'] = 'required_if:DGoods.DgFlag,Y|string';
        $rules['DGoods.Place_Sig'] = 'required_if:DGoods.DgFlag,Y|string';
        $rules['DGoods.Title_Sig'] = 'required_if:DGoods.DgFlag,Y|string';

        $rules['DryIce.Flag'] = 'sometimes|in:N,Y';
        $rules['DryIce.Weight'] = 'required_if:DryIce.Flag,Y|numeric|min:0.01';

        $rules['Alcohol.Flag'] = 'in:Y,N';
        $rules['Alcohol.Type'] = 'required_if:Alcohol.Flag,Y|string';
        $rules['Alcohol.Packaging'] = 'required_if:Alcohol.Flag,Y|string';
        $rules['Alcohol.Volume'] = 'required_if:Alcohol.Flag,Y|numeric';
        $rules['Alcohol.Quantity'] = 'required_if:Alcohol.Flag,Y|numeric';

        if (isset($shipment['CustomsClearanceDetail'])) {
            $rules['CustomsClearanceDetail.TermsOfSale'] = 'required|in:FCA,CIP,CPT,EXW,DDP';
            $rules['CustomsClearanceDetail.DutiesPayment.Payor.ResponsibleParty.AccountNumber'] = 'required|alpha_num';
            $rules['CustomsClearanceDetail.DutiesPayment.Payor.ResponsibleParty.Contact'] = 'required|string';
            $rules['CustomsClearanceDetail.CustomsValue.Currency'] = 'required|alpha:size:3';
            $rules['CustomsClearanceDetail.CustomsValue.Amount'] = 'required|numeric';
            $rules['CustomsClearanceDetail.Commodities.NumberOfPieces'] = 'required|integer';
            $rules['CustomsClearanceDetail.Commodities.Description'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|string';
            $rules['CustomsClearanceDetail.Commodities.CountryOfManufacture'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|alpha|size:2';
            $rules['CustomsClearanceDetail.Commodities.Weight.Units'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|in:KG,LB';
            $rules['CustomsClearanceDetail.Commodities.Weight.Value'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|numeric';
            $rules['CustomsClearanceDetail.Commodities.Quantity'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|integer';
            $rules['CustomsClearanceDetail.Commodities.QuantityUnits'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|in:EA';
            $rules['CustomsClearanceDetail.Commodities.UnitPrice.Currency'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|alpha|size:3';
            $rules['CustomsClearanceDetail.Commodities.UnitPrice.Amount'] = 'required_with:CustomsClearanceDetail.Commodities.NumberOfPieces|numeric';
        }
        
        $rules['LabelSpecification.LabelFormatType'] = 'in:STANDARD,DATA';
        $rules['LabelSpecification.ImageType'] = 'required|in:PDF';
        $rules['LabelSpecification.LabelStockType'] = 'required|in:A4,6X4';

        return $rules;
    }

    private function validate_address($address, $address_type = 'Recipient') {

        $errors = '';

        $rules = $this->add_address_rules($address_type);
        $address_validation = Validator::make($address, $rules);

        if ($address_validation->fails()) {
            $errors = $this->build_validation_errors($pickup_validation->errors());
        }

        return $errors;
    }

    private function validate_pickup($data) {

        $errors = '';

        $rules = $this->add_contact_rules('Shipper');
        $rules = $this->add_address_rules('Shipper', $rules);

        // Add any additional rules
        $rules['UserID'] = 'required|integer';
        $rules['Carrier'] = 'required|integer';
        $rules['PackageLocation'] = 'required|string|max:45';
        $rules['PickupDate'] = 'required|string|max:2';
        $rules['TimeAvailable'] = 'required|string|max:45';
        $rules['CloseTime'] = 'required|string|max:2';
        $pickup_validation = Validator::make($address, $rules);

        if ($pickup_validation->fails()) {
            $errors = $this->build_validation_errors($pickup_validation->errors());
        }

        return $errors;
    }

    private function validate_shipment($shipment) {

        $errors = '';

        $rules = $this->add_shipment_rules($shipment);

        // Do Generic validation
        $shipment_validation = Validator::make($shipment, $rules);

        if ($shipment_validation->fails()) {

            // Return errors as an array
            $errors = $this->build_validation_errors($shipment_validation->errors());
        }

        // Do validation based on Girth
        $errors = $this->check_girth($shipment, $errors);

        return $errors;
    }

    private function check_girth($shipment, $errors = '') {

        /*
         * ***********************************************************************************************
         *  Validate DIMS - Carrier Supplied Box (allow DIMS to be entered in as zero or the correct DIMS)
         *
         * S Ireland 13-01-2016 10:48
         *
         * There are maximum weight and size limits on some of the services – for example the UPS services
         * Express, Express Saver and Standard have maximum weight per piece of 70 kilos and the same
         * dimensional constraints that apply to the Fedex IP service.
         *
         * If a Customer tries to book anything with a single piece weight in excess of 70 kilos or whose
         * single piece volumetric weight is in excess of 70 kilos it cannot travel on the services above.
         * The girth calculation also applies – Longest dimension plus twice the other two dimensions
         * cannot exceed 330 cms.
         * ***********************************************************************************************
         */
        switch ($shipment['Service']) {

            case "NI24":
            case "IE48":
            case "UK24P":
            case "IPF":
            case "AIR":
                $allowedWeight = 2000;
                $allowedGirth = 999;
                $maxDim = 999;
                $divisor = 5000;
                break;

            case "RM48":
                $allowedWeight = 2;
                $allowedGirth = 999;
                $maxDim = 999;
                $divisor = 5000;
                break;

            default:
                $allowedWeight = 70;
                $allowedGirth = 330;
                $maxDim = 150;
                $divisor = 5000;
                break;
        }

        // Check Packaging is within Carrier defined limits.
        for ($i = 1; $i < $shipment['PackageCount']; $i++) {
            $volWeight = $shipment['Packages']['Package'][$i]['Length'] * $shipment['Packages']['Package'][$i]['Width'] * $shipment['Packages']['Package'][$i]['Height'];
            $volWeight = $volWeight / $divisor;
            $maxWeight = max($shipment['Packages']['Package'][$i]['Weight'], $volWeight);
            $maxDim = max($shipment['Packages']['Package'][$i]['Length'], $shipment['Packages']['Package'][$i]['Width'], $shipment['Packages']['Package'][$i]['Height']);
            $girth = (($shipment['Packages']['Package'][$i]['Length'] + $shipment['Packages']['Package'][$i]['Width'] + $shipment['Packages']['Package'][$i]['Height']) * 2) - $maxDim;
            if ($maxWeight > $allowedWeight) {
                $errors[] = "Package $i Weight exceeds Max allowed $allowedWeight Kgs.";
            }
            if ($girth > $allowedGirth) {
                $errors[] = "Package $i Girth exceeds Max allowed $allowedGirth cms.";
            }
        }

        return $errors;
    }

    private function build_validation_errors($messages) {

        foreach ($messages->all() as $message) {
            $errors[] = $message;
        }

        return $errors;
    }

    /*
     * ************************************
     * Generic Functions
     * ************************************
     */

    public function __construct() {
        
    }

    public function build_carrier($carrier = "DHL", $mode = "TEST") {

        switch ($carrier) {
            case "FEDEX":
                $this->carrier = new FedexAPI($mode);
                break;

            case "UPS":
                $this->carrier = new UPSAPI($mode);
                break;

            case "DHL":
                $this->carrier = new DHLWebAPI($mode);
                break;

            case "EASYPOST":
                $this->carrier = new EasyPostAPI($mode);
                break;

            default:
                // Do Nothing
                break;
        }
    }

    private function calc_vol($packages) {

        $tot_vol = 0;
        if (isset($packages[0]['SequenceNumber'])) {
            foreach ($packages as $package) {
                $length = $package['Length'];
                $width = $package['Width'];
                $height = $package['Height'];

                $vol = $length * $width * $height / $this->vol_divisor;

                $tot_vol += $this->roundup_half($vol);
            }
        } else {
            $tot_vol = $packages['Length'] * $packages['Width'] * $packages['Height'] / 5000;
        }

        return $tot_vol;
    }

    private function roundup_half($vol) {

        $rem = fmod($vol, 1);
        if ($rem <> 0) {
            if ($rem <= 0.5) {
                $vol = floor($vol) + .5;
            } else {
                $vol = ceil($vol);
            }
        }

        return $vol;
    }

    private function create_xml($data) {
        $xml = new XmlWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'utf-8');
        $xml->startElement('stats');

        $this->write_xml($xml, $data);

        $xml->endElement();
        $response = $xml->outputMemory(true);

        return $response;
    }

    private function write_xml(XMLWriter $xml, $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $xml->startElement($key);
                $this->write_xml($xml, $value);
                $xml->endElement();
                continue;
            }

            $xml->writeElement($key, $value);
        }
    }

    public function selectBestCarrier(&$shipment) {

        $carrier = strtoupper($shipment['Carrier']);
        $orig_country = $shipment['Shipper']['Address']['CountryCode'];
        $dest_country = $shipment['Recipient']['Address']['CountryCode'];
        $orig_postcode = $shipment['Shipper']['Address']['PostCode'];
        $dest_postcode = $shipment['Recipient']['Address']['PostCode'];
        $service = $shipment['Service'];

        $default = [
            'NI24' => 'IFS',
            'NI48' => 'IFS',
            'IE48' => 'IFS',
            'UK24' => 'UPS',
            'UK48' => 'FEDEX',
            'UK48P' => 'IFS',
            'IP' => 'AUTO',
            'IPF' => 'FEDEX',
            'IE' => 'FEDEX',
        ];

        // If AUTO try to select a service
        if ($carrier == 'AUTO') {
            $carrier = $default[$service];

            // If Multiple carriers available
            // then try again
            if ($carrier == 'AUTO') {
                switch ($service) {
                    case 'IP':
                        $carrier = 'FEDEX';
                        break;

                    default:
                        $carrier = '';
                        break;
                }
            }
        }

        return $carrier;
    }

    private function add_shipment($data, $direction, $response) {

        $carrier = Carrier::where('carrier_code', $data['Carrier'])->first();

        // Created Label so store details
        $time_stamp = date('Y-m-d H:i:s');

        // Update Shipment Table

        $shipment_id = DB::table('shipments')->insertGetId([
            "consignmentno" => $response['AirwayBill'],
            "carrier_id" => $carrier->id,
            "company_id" => '1',
            "user_id" => $data['UserID'],
            "status" => 'Awaiting Pickup',
            "shipper_company" => $data['Shipper']['Contact']['CompanyName'],
            "shipper_address1" => $data['Shipper']['Address']['AddressLine1'],
            "shipper_address2" => $data['Shipper']['Address']['AddressLine2'],
            "shipper_address3" => $data['Shipper']['Address']['AddressLine3'],
            "shipper_city" => $data['Shipper']['Address']['City'],
            "shipper_county" => $data['Shipper']['Address']['County'],
            "shipper_postcode" => $data['Shipper']['Address']['PostCode'],
            "shipper_country" => $data['Shipper']['Address']['CountryCode'],
            "shipper_residential" => $data['Shipper']['Address']['Residential'],
            "shipper_contact" => $data['Shipper']['Contact']['PersonName'],
            "shipper_email" => $data['Shipper']['Contact']['Email'],
            "shipper_phone" => $data['Shipper']['Contact']['PhoneNumber'],
            "shipper_account" => $data['Shipper']['Contact']['Account'],
            "consignee_company" => $data['Recipient']['Contact']['CompanyName'],
            "consignee_address1" => $data['Recipient']['Address']['AddressLine1'],
            "consignee_address2" => $data['Recipient']['Address']['AddressLine2'],
            "consignee_address3" => $data['Recipient']['Address']['AddressLine3'],
            "consignee_city" => $data['Recipient']['Address']['City'],
            "consignee_county" => $data['Recipient']['Address']['County'],
            "consignee_postcode" => $data['Recipient']['Address']['PostCode'],
            "consignee_country" => $data['Recipient']['Address']['CountryCode'],
            "consignee_residential" => $data['Recipient']['Address']['Residential'],
            "consignee_contact" => $data['Recipient']['Contact']['PersonName'],
            "consignee_email" => $data['Recipient']['Contact']['Email'],
            "consignee_phone" => $data['Recipient']['Contact']['PhoneNumber'],
            "consignee_account" => $data['Recipient']['Contact']['Account'],
            "customer_reference" => $data['Reference'],
            "contents" => $data['Contents'],
            "pack_count" => $data['PackageCount'],
            "total_weight" => $data['TotalWeight'],
            "total_volume" => $this->calc_vol($data['Packages']['Package']),
            "weightunits" => $data['WeightUnits'],
            "service_code" => $data['Service'],
            "carrier_service" => $data['Service'],
            "options" => '',
            "is_dutiable" => $data['IsDutiable'],
            "insured_amount" => $data['InsuredAmount'],
            "currency_code" => $data['CurrencyCode'],
            "payor_freight" => $data['PayorFreight'],
            "payor_duty" => $data['PayorDuty'],
            "order_id" => '',
            "guaranteed" => false,
            "customs_info" => '',
            "created_by" => $data['UserID'],
            "date_available" => $data['ShipDate'],
            "time_available" => date('H:i:s'),
            "collected" => false,
            "created_at" => $time_stamp,
            "updated_at" => $time_stamp
        ]);

        if ($shipment_id > 0) {

            // Write each Packages details
            for ($i = 0; $i < $data['PackageCount']; $i++) {

                if ($data['PackageCount'] > 1) {
                    $length = $data['Packages']['Package'][$i]['Length'];
                    $width = $data['Packages']['Package'][$i]['Width'];
                    $height = $data['Packages']['Package'][$i]['Height'];
                    $vol_weight = $length * $width * $height / $this->vol_divisor;
                    $package_id = DB::table('packages')->insertGetId([
                        "shipment_id" => $shipment_id,
                        "packagetype" => $data['PackagingType'],
                        "dimunits" => $data['DimensionUnits'],
                        "length" => $length,
                        "width" => $width,
                        "height" => $height,
                        "weightunits" => $data['WeightUnits'],
                        "weight" => $data['Packages']['Package'][$i]['Weight'],
                        "volWeight" => $vol_weight,
                        "package_scan" => '',
                        "dryice_weight" => '',
                        "bio" => 'N',
                        "created_at" => $time_stamp,
                        "updated_at" => $time_stamp
                    ]);
                } else {

                    $package_id = DB::table('packages')->insertGetId([
                        "shipment_id" => $shipment_id,
                        "packagetype" => $data['PackagingType'],
                        "dimunits" => $data['DimensionUnits'],
                        "length" => $data['Packages']['Package']['Length'],
                        "width" => $data['Packages']['Package']['Width'],
                        "height" => $data['Packages']['Package']['Height'],
                        "weightunits" => $data['WeightUnits'],
                        "weight" => $data['Packages']['Package']['Weight'],
                        "volWeight" => $data['Packages']['Package']['Weight'],
                        "package_scan" => $data['Packages']['Package']['PackageNo'],
                        "dryice_weight" => '',
                        "bio" => 'N',
                        "created_at" => $time_stamp,
                        "updated_at" => $time_stamp
                    ]);
                }
            }

            // Write Label data
            $label_id = DB::table('labels')->insertGetId([
                "shipment_id" => $shipment_id,
                "label_type" => $direction,
                "label_format" => $response['LabelFormat'],
                "label_base64" => $response['LabelBase64'],
                "created_at" => $time_stamp,
                "updated_at" => $time_stamp
            ]);

            // Write order details if provided
            if (isset($data['Orders'])) {
                $order_id = DB::table('orders')->insertGetId([
                    "patient_name" => $data['Orders']['Order']['Patient'],
                    "order_number" => $data['Orders']['Order']['order_number'],
                    "order_summary" => $data['Orders']['Order']['order_summary'],
                    "order_specification" => $data['Orders']['Order']['order_specifications'],
                    "order_date" => date('Y-m-d'),
                    "shipment_id" => $shipment_id,
                    "lab_id" => $data['Orders']['Order']['lab_id'],
                    "created_at" => $time_stamp,
                    "updated_at" => $time_stamp
                ]);

                if ($order_id > 0) {
                    // Write order_id back into the Shipment record created above.
                    DB::table('shipments')->where('id', $shipment_id)->update(['order_id' => $order_id]);
                }
            }
        }
    }

    private function add_pickup($carrier_id, $data, $response) {

        $carrier = Carrier::where('carrier_code', $data['Carrier'])->first();

        $pickup = new Pickup;

        $pickup->user_id = $data['UserID'];
        $pickup->carrier_id = $carrier->id;
        $pickup->pickup_ref = $response['PickupRef'];
        $pickup->contact = $data["Contact"];
        $pickup->company = $data["CompanyName"];
        $pickup->address1 = $data["Address1"];
        $pickup->address2 = $data["Address2"];
        $pickup->address3 = $data["Address3"];
        $pickup->city = $data["City"];
        $pickup->county = $data["County"];
        $pickup->country = $data["CountryCode"];
        $pickup->postcode = $data["PostCode"];
        $pickup->phone = $data["Phone"];
        $pickup->account = $data["Account"];
        $pickup->location = "Reception";
        $pickup->pickup_date = $data['PickupDate'];
        $pickup->time_available = $data['TimeAvailable'];
        $pickup->close_time = $data['CloseTime'];
        $pickup->status = 'A';
        $pickup->save();
    }

    private function generate_errors($response, $errors) {

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

    /*
     * *********************************************
     * *********************************************
     * Start of Interface Calls
     * *********************************************
     * *********************************************
     */

    public function checkAddress($address, $format = "JSON", $carrier = "FEDEX") {

        $response = '';
        $errors = $this->validate_address($shipment);

        if ($errors == '') {
            $this->build_carrier($data['Carrier']);                             // Create Carrier Object
            $response = $this->carrier->checkAddress($address);                 // Send Message to Carrier
        } else {
            $response = $this->generate_errors($response, $errors);
        }

        return $response;
    }

    public function checkAvailServices($shipment) {

        $response = '';
        $errors = $this->validate_shipment($shipment);

        if ($errors == '') {
            $this->build_carrier($data['Carrier']);                             // Create Carrier Object
            $response = $this->carrier->checkAvailServices($shipment);          // Send Message to Carrier
        } else {
            $response = $this->generate_errors($response, $errors);
        }

        return $response;
    }

    public function createShipment($shipment, $direction = 'O', $format = "JSON") {

        $response = '';
        $errors = $this->validate_shipment($shipment);

        if ($errors == '') {

            $shipment['Carrier'] = $this->selectBestCarrier($shipment);

            if ($shipment['Carrier'] > '') {
                $this->build_carrier($shipment['Carrier']);                     // Create Carrier Object

                $response = $this->carrier->createShipment($shipment);          // Send Shipment to Carrier

                if ($response['Result'] == 'SUCCESS') {

                    $this->add_shipment($shipment, $direction, $response);
                }
            } else {

                $response = $this->generate_errors($response, 'Unable to select Service');
            }
        } else {

            $response = $this->generate_errors($response, $errors);
        }

        return $response;
    }

    public function requestPickup($data) {

        $response = '';
        $errors = $this->validate_pickup($shipment);

        if ($errors == '') {
            // Get Carrier using the Carriers alpha Code
            $carrier = Carrier::where('carrier_code', $data['Carrier'])->first();

            if (isset($carrier)) {
                // Check we dont have any pickups already scheduled
                $count = Pickup::where('user_id', $data['UserID'])
                        ->where('carrier_id', $carrier->id)
                        ->where('status', 'A')
                        ->where('pickup_date', $data['PickupDate'])
                        ->count();

                // Pick up does not already exist
                if ($count == 0) {

                    // Create Carrier Object
                    $this->build_carrier($data['Carrier']);

                    // Send Message to Carrier
                    $response = $this->carrier->requestPickup($data);

                    if ($response['Result'] == 'SUCCESS') {
                        $this->add_pickup($data, $response);
                    }
                } else {
                    $response = $this->generate_errors($response, "Pickup already requested for " . $data['PickupDate']);
                }
            } else {
                $response = $this->generate_errors($response, "Unknown Carrier");
            }
        } else {
            $response = $this->generate_errors($response, $errors);
        }


        return $response;
    }

    public function cancelPickup($data, $format = "JSON") {

        $carrier = strtoupper($data['Carrier']);
        $this->build_carrier($carrier);                                           // Create Carrier Object

        $pickup = Pickup::findorFail($data['UserID']);

        // Create Carrier Object
        $this->build_carrier($data['Carrier']);

        // Send Message to Carrier
        $response = $this->carrier->cancelPickup($data);

        $response['Result'] = 'SUCCESS';

        if ($response['Result'] == 'SUCCESS') {
            $pickup->cancelled_at = date('Y-m-d H:i:s');
            $pickup->status = 'C';
            $pickup->save();
        }

        return $response;
    }

}
