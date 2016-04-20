<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use \EasyPost\EasyPost;

class HomeController extends Controller {

    private $api_key = "9400110898825022579493";

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('home');
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
    public function test() {

        $format = 'Y-m-d H:i:s';
        $mdate = '2016-03-14 19:15:00';
        $tz = 'America/Los_Angeles';
        $gmt = 'Europe/London';

        $mydate = Carbon::createFromFormat($format, $mdate, $gmt);
        $pdate = Carbon::now()->setTimezone($tz);
        $ndate = $mydate->setTimezone($tz);
        $offset = $mydate->diff($pdate);
        $offset = $offset->format('H:i:s');

        echo "MDate : $mdate PDate : $pdate NDate : $ndate Offset : $offset<br>";


        /*
        EasyPost::setApiKey($this->api_key);

        $tracker = \EasyPost\Tracker::create(array(
                    "tracking_code" => "9400110898825022579493",
                    "carrier" => "DHL"
        ));

        dd($tracker);

         */
    }

}
