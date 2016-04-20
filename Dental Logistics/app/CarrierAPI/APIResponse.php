<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\CarrierAPI;

use Illuminate\Support\Facades\Response;

/**
 * Description of Respond
 *
 * @author gmcbroom
 */
class APIResponse {

    public $statusCode = '200';
    public $transactionHeader = 'IFS API Transaction';

    private function setVersion() {
        return "1.0";
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode() {

        return $this->statusCode;
    }

    public function respondUnAuthorized($message = 'UnAuthorized') {
        return $this->setStatusCode(401)->respondWithError($message);
    }

    public function respondInvalid($message = 'Validation Errors') {
        return $this->setStatusCode(401)->respondWithError($message);
    }

    public function respondNotFound($message = 'Not Found') {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    public function getValue(&$item, $default = '') {
        return (!empty($item)) ? $item : $default;
    }

    public function respondCreatedShipment($reply = '') {

        if ($reply['Result'] == 'SUCCESS') {
            return $this->setStatusCode(201)->respond($reply);
        } else {
            return $this->setStatusCode(400)->respondWithError($reply);
        }
    }

    public function respond($data, $headers = []) {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    public function respondWithError($errors) {

        return $this->respond([
                    'TransactionHeader' => $this->transactionHeader,
                    'Result' => 'ERROR',
                    'Errors' => $errors,
                    'Version' => $this->setVersion(),
                    'status_code' => $this->getStatusCode()
        ]);
    }

}
