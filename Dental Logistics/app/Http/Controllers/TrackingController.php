<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ScaneventController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function easypost_webhook() {
        //
        {

            $input = $request->all();
                    
            /*
              "id": "trk_Txyy1vaM",
              "object": "Tracker",
              "mode": "production",
              "tracking_code": "1Z204E38YW95204424",
              "status": "delivered",
              "created_at": "2014-11-18T10:51:54Z",
              "updated_at": "2014-11-18T10:51:54Z",
              "signed_by": "John Tester",
              "weight": 17.6,
              "est_delivery_date": "2014-08-27T00:00:00Z",
              "shipment_id": null,
              "carrier": "UPS",
              "tracking_details": [
              {
              "object": "TrackingDetail",
              "message": "BILLING INFORMATION RECEIVED",
              "status": "pre_transit",
              "datetime": "2014-08-21T14:24:00Z",
              "tracking_location": {
              "object": "TrackingLocation",
              "city": null,
              "state": null,
              "country": null,
              "zip": null
              },
              {
              "object": "TrackingDetail",
              "message": "DELIVERED",
              "status": "delivered",
              "datetime": "2014-08-24T15:33:00Z",
              "tracking_location": {
              "object": "TrackingLocation",
              "city": "SAN FRANCISCO",
              "state": "CA",
              "country": "US",
              "zip": null
              }
              }
              }
             * 
             */
        }
    }

}
