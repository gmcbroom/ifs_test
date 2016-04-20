<?php

namespace App\Http\Controllers;

use Auth;
use App\Address;
use App\Carrier;
use App\Pickup;
use App\ServiceLevel;
use App\CarrierAPI\CarrierAPI;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;

class PickupController extends Controller {

    private $page_size = 25;

    private function setAPIAuth() {

        $auth = array(
            'UserID' => 'GMCB',
            'Password' => 'SECRET',
            'Token' => 'MYTOKEN'
        );

        return $auth;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($message = '') {

        $user = Auth::user();
        // $message = '';
        // Allow Admin user to see all Shipments
        if ($user->admin) {
            $pickups = Pickup::orderBy('created_at', 'desc')->paginate($this->page_size);
            return view('pickup.index', compact('pickups'));
        } else {
            $pickups = Pickup::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate($this->page_size);
            return view('pickup.index', compact('pickups'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

        $user = Auth::user();
        $message = '';
        $times = [
            "09:00:00" => "09:00AM",
            "10:00:00" => "10:00AM",
            "11:00:00" => "11:00AM",
            "12:00:00" => "12:00AM",
            "13:00:00" => "01:00PM",
            "14:00:00" => "02:00PM",
            "15:00:00" => "03:00PM",
            "16:00:00" => "04:00PM",
            "17:00:00" => "05:00PM"
        ];

        $carriers = Carrier::where('live', 'Y')->orderBy('name')->lists('name', 'name');

        return view('/pickup/create', compact('carriers', 'times', 'message'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $user = Auth::user();
        $response = $this->request_pickup($request);

        if (isset($response['Notifications']['Errors'])) {

            $carriers = Carrier::where('live', 'Y')->orderBy('name')->lists('name', 'name');
            return redirect('/pickup.create')->withErrors($response['Notifications']['Errors'])->withInput();
        } else {
            // Allow Admin user to see all Shipments
            if ($user->admin) {
                $pickups = Pickup::orderBy('created_at', 'desc')->paginate($this->page_size);
            } else {
                $pickups = Pickup::where('user_id', $user->id)->orderBy('created_by', 'desc')->paginate($this->page_size);
            }
            return view('/pickup.index', compact('pickups'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
//
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
//
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
//
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $user = Auth::user();
        $auth = $this->setAPIAuth();

        $pickup = Pickup::findOrFail($id);

        $data["UserID"] = $user->id;
        $data['Carrier'] = $pickup->carrier->name;
        $data['PickupRef'] = $pickup->pickup_ref;
        $data["Contact"] = $pickup->contact;
        $data["Reason"] = '006';
        $data["PickupDate"] = $pickup->pickup_date;
        $data["CountryCode"] = $pickup->country;

        $request = [
            'Auth' => $auth,
            'Data' => $data
        ];

        $carrierAPI = new CarrierAPI;

        $response = $carrierAPI->cancelPickup($request);

        return redirect('/pickup');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function request_pickup(Request $request) {

        $user = Auth::user();
        $auth = $this->setAPIAuth();

        $carrier_id = $request->input('carrier_id');
        $pickup_date = $request->input('pickup_date');
        $time_available = $request->input('time_available');
        $close_time = $request->input('close_time');

        $carrier = Carrier::find($carrier_id);

        $data["UserID"] = $user->id;
        $data["Carrier"] = $carrier_id;
        $data["Contact"] = $user->first_name . " " . $user->last_name;
        $data["CompanyName"] = $user->company;
        $data["Address1"] = $user->address1;
        $data["Address2"] = $user->address2;
        $data["Address3"] = $user->address3;
        $data["City"] = $user->city;
        $data["County"] = $user->county;
        $data["CountryCode"] = $user->country;
        $data["PostCode"] = $user->postcode;
        $data["Phone"] = $user->phone;
        $data["PackageLocation"] = "Reception";
        $data["PickupDate"] = $pickup_date;
        $data["TimeAvailable"] = $time_available;
        $data["CloseTime"] = $close_time;

        $request = [
            'Auth' => $auth,
            'Data' => $data
        ];
        $carrierAPI = new CarrierAPI;

        // Send Request to Carrier
        $response = $carrierAPI->requestPickup($request);

        return $response;
    }

}
