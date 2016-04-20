@extends('master')

@section('content')
<h1>Create new Address</h1>

<hr/>
@include('partials.list')
    {!! Form::open(['url' => 'address/create']) !!}
        @include('addresses.form', ['submitButtonText' => 'Add Address'])
    {!! Form::close() !!}

@stop