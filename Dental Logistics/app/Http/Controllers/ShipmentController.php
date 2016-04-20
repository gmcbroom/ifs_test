<?php

namespace App\Http\Controllers;

use Auth;
use App\ClosingTime;
use App\Country;
use App\Shipment;
use App\Package;
use App\Pickup;
use App\ServiceLevel;
use App\Address;
use App\Label;
use App\CarrierAPI\CarrierAPI;
use Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ShipmentController extends Controller {

    private $yesno = '';
    private $vol_divisor = 5000;
    private $page_size = 25;
    private $pkg_type = 'CTN';

    public function __construct() {
        $this->yesno = [0 => 'N', 1 => 'Y'];
    }

    /**
     * Returns a dummy string
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $user = Auth::user();
        $message = '';

        // Allow Admin user to see all Shipments
        if ($user->admin) {
            $shipments = Shipment::paginate($this->page_size);

            return view('shipments.index', compact('shipments'));
        } else {
            $shipments = Shipment::where('user_id', $user->id)->orderBy('created_by')->paginate($this->page_size);
            return view('shipments.index', compact('shipments'));
        }
    }

    /**
     * Shipment Creation screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

        $message = '';
        $addresses = Address::orderBy('company')->lists('company', 'id');
        $service_levels = ServiceLevel::orderby('display_order')->where('active', 'y')->lists('name', 'id');
        return view('shipments.create', compact('addresses', 'service_levels', 'message'));
    }

    /**
     * Save an entered shipment.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $this->validate($request, [
            'patient' => 'required|string',
            'order_number' => 'required|string',
            'order_summary' => 'required|string',
            'order_specifications' => 'required|string',
            'lab_id' => 'required|integer',
            'pkg_length' => 'required|numeric|min:0.1|max:9999',
            'pkg_width' => 'required|numeric|min:0.1|max:9999',
            'pkg_height' => 'required|numeric|min:0.1|max:9999',
            'pkg_weight' => 'required|numeric|min:0.1|max:9999',
            'next_visit' => 'required|date',
            'service_level' => 'required|integer'
        ]);

        $response = $this->send_shipment($request);

        $message = '';

        if (isset($response['Errors']) && ($response['Errors'] != '')) {
            return redirect('/ship/create')->withErrors($response['Errors'])->withInput();
        } else {

            if (isset($response['AirwayBill'])) {
                $message = "AirwayBill : " . $response['AirwayBill'];
            }

            // if (isset($response['PickupDate'])) {
            //     $info = "Pickup : " . $response['PickupDate'] . ' before : ' . $response['PickupTime'];
            // }
            $addresses = Address::orderBy('company')->lists('company', 'id');
            $service_levels = ServiceLevel::orderby('display_order')->where('active', 'y')->lists('name', 'id');

            return view('shipments.create', compact('addresses', 'service_levels', 'message', 'info'));
        }
    }

    private function send_shipment($request) {

        $user = Auth::user();

        $lab = Address::find($request->input('lab_id'));
        $service = ServiceLevel::find($request->input('service_level'));

        $auth = $this->setAPIAuth();

        $temp = $request->all();

        $shipment = [
            'UserID' => $user->id,
            'ShipDate' => date('Y-m-d'),
            'Carrier' => 'DHL',
            'Pickup' => 'Y',
            'Service' => $service->service,
            'PackagingType' => 'OWN',
            'TotalWeight' => $request->input('pkg_weight'),
            'TotalVolWeight' => 2.0,
            'WeightUnits' => 'KG',
            'DimensionUnits' => 'CM',
            'Reference' => $request->input('order_number'),
            'Contents' => 'Dental',
            'IsDutiable' => 'N',
            'InsuredAmount' => '0',
            'CurrencyCode' => 'GBP',
            'Orders' => [
                'Order' => [
                    'Patient' => $request->input('patient'),
                    'order_number' => $request->input('order_number'),
                    'order_summary' => $request->input('order_summary'),
                    'order_specifications' => $request->input('order_specifications'),
                    'lab_id' => $request->input('lab_id')
                ]
            ],
            'Shipper' => [
                'Contact' => [
                    'PersonName' => $user->first_name . ' ' . $user->last_name,
                    'CompanyName' => $user->company,
                    'Email' => $user->email,
                    'PhoneNumber' => $user->phone
                ],
                'Address' => [
                    'AddressLine1' => $user->address1,
                    'AddressLine2' => $user->address2,
                    'AddressLine3' => $user->address3,
                    'City' => $user->city,
                    'County' => $user->county,
                    'PostCode' => $user->postcode,
                    'CountryCode' => 'GB',
                    'CountryName' => 'UnitedKingdom',
                    'Residential' => $this->yesno[$user->residential]
                ]
            ],
            'Recipient' => [
                'Contact' => [
                    'PersonName' => $lab->contact,
                    'CompanyName' => $lab->company,
                    'Email' => $lab->email,
                    'PhoneNumber' => $lab->phone
                ],
                'Address' => [
                    'AddressLine1' => $lab->address1,
                    'AddressLine2' => $lab->address2,
                    'AddressLine3' => $lab->address3,
                    'City' => $lab->city,
                    'County' => $lab->county,
                    'PostCode' => $lab->postcode,
                    'CountryCode' => 'GB',
                    'CountryName' => 'UnitedKingdom',
                    'Residential' => $this->yesno[$lab->residential]
                ]
            ],
            'PaymentDetails' => [
                'PaymentType' => 'SENDER',
                'Payor' => [
                    'ResponsibleParty' => [
                        'AccountNumber' => '604530164',
                        'Contact' => null,
                        'Address' => null
                    ]
                ]
            ],
            'PackageCount' => 1,
            'PackageDetail' => 'INDIVIDUAL_PACKAGES',
            'PackageLineItems' => [
                '0' => [
                    'SequenceNumber' => '1',
                    'PkgType' => $this->pkg_type,
                    'Weight' => $request->input('pkg_weight'),
                    'Dimensions' => [
                        'Length' => $request->input('pkg_length'),
                        'Width' => $request->input('pkg_width'),
                        'Height' => $request->input('pkg_height')
                    ]
                ]
            ],
            'SpecialServices' => [
                'SpecialServiceCodes' => ['A', 'I'],
            ],
            'CustomsClearanceDetail' => [
                'DutiesPayment' => [
                    'PaymentType' => 'RECIPIENT',
                    'Payor' => [
                        'ResponsibleParty' => [
                            'AccountNumber' => '604530164',
                            'Contact' => ''
                        ]
                    ]
                ],
                'CustomsValue' => [
                    'Amount' => '0.00',
                    'Currency' => 'GBP'
                ],
            ],
            'LabelSpecification' => [
                // 'LabelFormatType' => 'STANDARD', // valid values STANDARD/ DATA_ONLY
                'ImageType' => 'PDF', // valid values DPL, EPL2, PDF, ZPLII and PNG
                'LabelStockType' => '6X4'
            ],
        ];

        $request = [
            'Auth' => $auth,
            'Data' => $shipment
        ];

        $errors = '';
        $carrierAPI = new CarrierAPI;
        $availServices = $carrierAPI->checkAvailServices($request['Data']);

        /*
          if ($availServices['PickupDate'] == $request['Data']['ShipDate']) {
          echo 'Pickup Date = Ship Date<br>';
          } else {
          echo 'Pickup Date ('.$availServices['PickupDate'].') != Ship Date ('.$request['Data']['ShipDate'].')<br>';
          }
         */

        if ($errors == '') {

            $response = $carrierAPI->createShipment($request['Data']);
            $response['PickupDate'] = $availServices['PickupDate'];
            $response['PickupTime'] = $availServices['PickupTime'];
        } else {

            // Return Errors
            $response = $this->generate_errors($errors);
        }

        return $response;
    }

    /**
     * Produce Label for shipment.
     *
     * @return \Illuminate\Http\Response
     */
    public function label($shipment_id, $direction = 'O') {

        $direction = strtoupper($direction);

        $user = Auth::user();
        $shipment = Shipment::findOrFail($shipment_id);

        // User must be admin or the owner of the shipment
        if ($user->admin || $user->id == $shipment->id) {

            // echo "Valid user for label<br>";
            $label = Label::where('shipment_id', $shipment_id)->where('label_type', $direction)->first();

            if (is_null($label)) {
                // echo "Label is null<br>";
                // Returns Label does not yet exist so need to create one and then print it.
                $response = $this->generate_returns_label($shipment_id);
                $label = Label::where('shipment_id', $shipment_id)->where('label_type', $direction)->first();
                $shipment->returns_label_printed = "Y";
                $shipment->save();
            }
        }

        if (is_null($label)) {
            return redirect('ship');
        } else {
            return response(base64_decode($label->label_base64))->header('Content-Type', 'application/pdf');
        }
    }

    private function setAPIAuth() {

        $auth = array(
            'UserID' => 'GMCB',
            'Password' => 'SECRET',
            'Token' => 'MYTOKEN'
        );

        return $auth;
    }

    /**
     * Generate Returns Label for Shipment
     *
     * @return \Illuminate\Http\Response
     */
    public function generate_returns_label($shipment_id) {

        $user = Auth::user();

        $shipment = Shipment::find($shipment_id);
        $package = Package::where('shipment_id', $shipment_id)->first();
        $shipper_country = Country::where('alpha2', $shipment->shipper_country)->first();
        $consignee_country = Country::where('alpha2', $shipment->consignee_country)->first();

        // Ensure Shipdate is not before today
        $ship_date = $shipment->date_available;
        $today = date('Y-m-d');

        if ($today > $ship_date) {
            $ship_date = $today;
        }

        // Set Auth parameters
        $auth = $this->setAPIAuth();

        $shipment = [
            'ShipmentID' => $shipment_id,
            'UserID' => $user->id,
            'ShipDate' => $shipment->date_available,
            'Carrier' => 'DHL',
            'Pickup' => 'N',
            'Service' => $shipment->service_code,
            'PackagingType' => 'OWN',
            'TotalWeight' => $shipment->total_weight,
            'TotalVolWeight' => $shipment->total_volume,
            'WeightUnits' => 'KG',
            'DimensionUnits' => 'CM',
            'Reference' => $shipment->customer_reference,
            'Contents' => $shipment->contents,
            'IsDutiable' => $shipment->is_dutiable,
            'InsuredAmount' => $shipment->insured_amount,
            'CurrencyCode' => $shipment->currency_code,
            'Shipper' => [
                'Contact' => [
                    'PersonName' => $shipment->consignee_contact,
                    'CompanyName' => $shipment->consignee_company,
                    'Email' => $shipment->consignee_email,
                    'PhoneNumber' => $shipment->consignee_phone
                ],
                'Address' => [
                    'AddressLine1' => $shipment->consignee_address1,
                    'AddressLine2' => $shipment->consignee_address2,
                    'AddressLine3' => $shipment->consignee_address3,
                    'City' => $shipment->consignee_city,
                    'County' => $shipment->consignee_county,
                    'PostCode' => $shipment->consignee_postcode,
                    'CountryCode' => $shipment->consignee_country,
                    'CountryName' => $consignee_country->name,
                    'Residential' => $this->yesno[$shipment->consignee_residential]
                ]
            ],
            'Recipient' => [
                'Contact' => [
                    'PersonName' => $shipment->shipper_contact,
                    'CompanyName' => $shipment->shipper_company,
                    'Email' => $shipment->shipper_email,
                    'PhoneNumber' => $shipment->shipper_phone
                ],
                'Address' => [
                    'AddressLine1' => $shipment->shipper_address1,
                    'AddressLine2' => $shipment->shipper_address2,
                    'AddressLine3' => $shipment->shipper_address3,
                    'City' => $shipment->shipper_city,
                    'County' => $shipment->shipper_county,
                    'PostCode' => $shipment->shipper_postcode,
                    'CountryCode' => $shipment->shipper_country,
                    'CountryName' => $shipper_country->name,
                    'Residential' => $this->yesno[$shipment->shipper_residential]
                ]
            ],
            'PaymentDetails' => [
                'PaymentType' => 'SENDER',
                'Payor' => [
                    'ResponsibleParty' => [
                        'AccountNumber' => '604530164',
                        'Contact' => null,
                        'Address' => null
                    ]
                ]
            ],
            'PackageCount' => 1,
            'PackageDetail' => 'INDIVIDUAL_PACKAGES',
            'PackageLineItems' => [
                '0' => [
                    'SequenceNumber' => '1',
                    'PkgType' => $this->pkg_type,
                    'Weight' => $package->weight,
                    'Dimensions' => [
                        'Length' => $package->length,
                        'Width' => $package->width,
                        'Height' => $package->height
                    ]
                ]
            ],
            'SpecialServices' => [
                'SpecialServiceCodes' => ['A', 'I', 'PT'],
            ],
            'CustomsClearanceDetail' => [
                'DutiesPayment' => [
                    'PaymentType' => 'RECIPIENT',
                    'Payor' => [
                        'ResponsibleParty' => [
                            'AccountNumber' => '604530164',
                            'Contact' => ''
                        ]
                    ]
                ],
                'CustomsValue' => [
                    'Amount' => '0.00',
                    'Currency' => 'GBP'
                ],
            ],
            'LabelSpecification' => [
                // 'LabelFormatType' => 'STANDARD', // valid values STANDARD/ DATA_ONLY
                'ImageType' => 'PDF', // valid values DPL, EPL2, PDF, ZPLII and PNG
                'LabelStockType' => '6X4'
            ],
        ];

        $request = [
            'Auth' => $auth,
            'Data' => $shipment
        ];

        $errors = '';
        $carrierAPI = new CarrierAPI;

        if ($errors == '') {

            $response = $carrierAPI->createShipment($request['Data'], 'R');             // Create Returns Shipment
        } else {

            // Return Errors
            $response = $this->generate_errors($errors);
        }

        return $response;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function auth() {

        $auth = \EasyPost\EasyPost::setApiKey('JhU5G2oFG0I6XsZBD9ZaCg');
        $address = \EasyPost\Address::create(array(
                    'name' => 'Dr. Steve Brule',
                    'street1' => '179 N Harbor Dr',
                    'city' => 'Redondo Beach',
                    'state' => 'CA',
                    'zip' => '90277',
                    'country' => 'US',
                    'email' => 'dr_steve_brule@gmail.com'
        ));

        return $address;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addr() {

        return $address;
    }

    public function easypost() {

        /*
          $auth = \EasyPost\EasyPost::setApiKey('JhU5G2oFG0I6XsZBD9ZaCg');
          $from_address = array(
          'residential' => false,
          'name' => $user['first_name'] . ' ' . $user['last_name'],
          'company' => $user['company'],
          'street1' => $user['address1'],
          'street2' => $user['address2'],
          'city' => $user['town'],
          'state' => $user['county'],
          'zip' => $user['postcode'],
          'country' => $user->country->id,
          'phone' => $user['phone'],
          'email' => $user['email']
          );

          $to_address = array(
          'residential' => false,
          'name' => $lab['contact'],
          'company' => $lab['company'],
          'street1' => $lab['address1'],
          'street2' => $lab['address2'],
          'city' => $lab['town'],
          'state' => $lab['county'],
          'zip' => $lab['postcode'],
          'country' => $lab['country'],
          'phone' => $lab['phone'],
          'email' => $lab['email']
          );

          $parcel = array(
          "predefined_package" => "",
          "length" => $input['pkgLen'],
          "width" => $input['pkgWidth'],
          "height" => $input['pkgHeight'],
          "weight" => $input['pkgWeight'],
          );

          $pickup = \EasyPost\Pickup::create(
          array(
          "address" => $from_address,
          "shipment" => $shipment,
          "reference" => $shipment->id,
          "max_datetime" => date("Y-m-d H:i:s"),
          "min_datetime" => date("Y-m-d H:i:s", strtotime('+1 day')),
          "is_account_address" => false,
          "instructions" => "Will be next to garage"
          )
          );

          $pickup->buy(array('carrier' => 'UPS', 'service' => 'Future-day Pickup'));
         */
    }

}
