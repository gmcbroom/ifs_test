<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */

Route::get('/', function () {
    return view('auth/login');
});

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
// Route::get('auth/register', 'Auth\AuthController@getRegister');
// Route::post('auth/register', 'Auth\AuthController@postRegister');
// Route::get('auth/terms', 'Auth\AuthController@getTerms');
// Route::post('auth/terms', 'Auth\AuthController@postTerms');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);

Route::get('profile', ['middleware' => 'auth', function() {
        // Only authenticated users may enter...
    }]);

// Test page
////Route::get('test', ['uses' => 'HomeController@test']);

// Home page
Route::get('home', ['middleware' => 'auth', 'uses' => 'HomeController@index']);

// Shipments
Route::get('ship', ['middleware' => 'auth', 'uses' => 'ShipmentController@index']);
Route::get('ship/create', ['middleware' => 'auth', 'uses' => 'ShipmentController@create']);
Route::post('ship/create', ['middleware' => 'auth', 'uses' => 'ShipmentController@store']);
Route::get('ship/{id}', ['middleware' => 'auth', 'uses' => 'ShipmentController@show']);
Route::post('ship/{id}/label/{direction?}', ['middleware' => 'auth', 'uses' => 'ShipmentController@label']);
Route::post('ship/{id}/cancel', ['middleware' => 'auth', 'uses' => 'ShipmentController@destroy']);

// Pickup Requests
Route::get('pickup', ['middleware' => 'auth', 'uses' => 'PickupController@index']);
Route::get('pickup/create', ['middleware' => 'auth', 'uses' => 'PickupController@create']);
Route::post('pickup/create', ['middleware' => 'auth', 'uses' => 'PickupController@store']);
Route::get('pickup/{id}', ['middleware' => 'auth', 'uses' => 'PickupController@show']);
Route::post('pickup/{id}/delete', ['middleware' => 'auth', 'uses' => 'PickupController@destroy']);

// Users
Route::get('user', ['middleware' => 'auth', 'uses' => 'UserController@index']);
Route::get('user/create', ['middleware' => 'auth', 'uses' => 'UserController@create']);
Route::get('user/{id}/edit', ['middleware' => 'auth', 'uses' => 'UserController@edit']);
Route::post('user/{id}/status', ['middleware' => 'auth', 'uses' => 'UserController@toggleStatus']);
Route::get('user/registered', ['middleware' => 'auth', 'uses' => 'UserController@registered']);
Route::get('user/{id}/verify', 'UserController@verify');
Route::get('user/{id}', ['middleware' => 'auth', 'uses' => 'UserController@show']);
Route::patch('user/{id}/update', ['middleware' => 'auth', 'uses' => 'UserController@update']);

// API Routes - Unauthenticated using api_token
//// Route::post('/api/v1/tracker/easypost', 'APIController@trackEasypost');
//// Route::get('/api/v1/tracker/easypost', 'APIController@trackEasypost');

// API Routes - Authenticated using api_token
//// Route::group(['prefix' => '/api/v1/', 'middleware' => ['auth:api']], function () {

////     Route::get('address/check/{mode}', 'APIController@checkAddress');
////     Route::post('address/check/{mode}', 'APIController@checkAddress');
////     Route::get('shipment/check/{mode}', 'APIController@checkShipments');
////     Route::post('shipment/check/{mode}', 'APIController@checkShipment');
////     Route::post('shipment/create/{mode}', 'APIController@createShipment');
////     Route::post('shipment/{id}/cancel/{mode}', 'APIController@cancelShipment');

//// });

/*
Route::get('/api/v1/shipment/testPDF', 'APIController@testPDF');
Route::get('tracker/test', 'APIController@trackTest');

// Resources
Route::resource('address', 'AddressController');
Route::resource('carrier', 'CarrierController');
Route::resource('country', 'CountryController');
Route::resource('currency', 'CurrencyController');
Route::resource('company', 'CompanyController');
Route::resource('depot', 'DepotController');
Route::resource('option', 'OptionController');
Route::resource('package', 'PackageController');
Route::resource('packagetype', 'PackagetypeController');
Route::resource('quote', 'QuoteController');
Route::resource('scandetail', 'ScandetailController');
Route::resource('scanevent', 'ScaneventController');
Route::resource('servicelevel', 'ServicelevelController');
Route::resource('shipment', 'ShipmentController');
*/
