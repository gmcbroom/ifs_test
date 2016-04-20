@extends('master')

@section('content')

    <h1>Addresses</h1>

    <hr/>

    @foreach ($addresses as $address)
        <article>
            {{ $address->name }}
        </article>
    @endforeach
@stop