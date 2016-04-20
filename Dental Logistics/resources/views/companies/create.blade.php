@extends('master')

@section('content')
<h1>Create new Customer</h1>

<hr/>
@include('partials.list')
    {!! Form::open(['url' => 'customer/create']) !!}
        @include('customers.form', ['submitButtonText' => 'Add Customer'])
    {!! Form::close() !!}

@stop