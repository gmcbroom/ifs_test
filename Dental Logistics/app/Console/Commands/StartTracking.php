<?php

namespace App\Console\Commands;

use EasyPost;
use Illuminate\Console\Command;

class StartTracking extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send docketno to EasyPost for tracking';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $tracking_code = "EZ4000000004";
        $carrier = "UPS";
        $tracker = \EasyPost\Tracker::create(array('tracking_code' => $tracking_code, 'carrier' => $carrier));

        /*

{
  "id": "trk_Txyy1vaM",
  "object": "Tracker",
  "mode": "test",
  "tracking_code": "EZ4000000004",
  "status": "delivered",
  "created_at": "2014-11-18T10:51:54Z",
  "updated_at": "2014-11-19T10:51:54Z",
  "signed_by": "John Tester",
  "weight": 17.6,
  "est_delivery_date": "2014-11-27T00:00:00Z",
  "shipment_id": null,
  "carrier": "UPS",
  "tracking_details": [
    {
      "object": "TrackingDetail",
      "message": "BILLING INFORMATION RECEIVED",
      "status": "pre_transit",
      "datetime": "2014-11-21T14:24:00Z",
      "tracking_location": {
        "object": "TrackingLocation",
        "city": null,
        "state": null,
        "country": null,
        "zip": null
      }
    }
  ]
}


         */
    }

}
