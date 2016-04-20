@extends('master')

@section('content')
<h1>Edit : {!! $customer->name !!}</h1>

@include('partials.list')

{!! Form::model($customer, ['method' => 'PATCH', 'action' => ['CustomerController@update', $customer->id]]) !!}
    @include('customers.form', ['submitButtonText' => 'Update Customer'])
{!! Form::close() !!}

@stop