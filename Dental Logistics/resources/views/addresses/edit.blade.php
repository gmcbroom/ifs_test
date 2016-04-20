@extends('master')

@section('content')
<h1>Edit : {!! $address->name !!}</h1>

@include('partials.list')

{!! Form::model($address, ['method' => 'PATCH', 'action' => ['AddressController@update', $address->id]]) !!}
    @include('addresses.form', ['submitButtonText' => 'Update Address'])
{!! Form::close() !!}

@stop