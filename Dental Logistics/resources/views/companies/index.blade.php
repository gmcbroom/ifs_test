@extends('master')

@section('content')

    <h1>Customers</h1>

    <hr/>

    @foreach ($customers as $customer)
        <article>
            {{ $customer->name }}
        </article>
    @endforeach
@stop