<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AddressRequest extends Request {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * (S)hipper, (R)ecipient
     *
     * @return array
     */
    public function rules() {
        return [
            'type' => 'required|in:S,R',
            'name' => 'required|string|min:3|max:40',
            'street1' => 'required|string|min:3|max:40',
            'street2' => 'string|min:3|max:40',
            'city' => 'required|string|min:3|max:40',
            'county' => 'alpha|min:3|max:40',
            'postcode' => 'string',
            'country_id' => 'required|integer',
            'contact' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string|min:11|max:14',
            'residential' => 'required|in:Y,N'
        ];
    }

}
