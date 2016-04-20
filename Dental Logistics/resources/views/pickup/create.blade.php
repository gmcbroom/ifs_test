@extends('master')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-8 text">
            <h1>Create Pickup Request</h1>
            <hr/>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="error_pane" >
                @include('partials.list')
            </div>

            {!! Form::open(['method' => 'POST', 'url' => '/pickup/create', 'class' => 'form-horizontal']) !!}

            <div class="form-group">
                {!! Form::label('carrier_id', 'Carrier', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::select('carrier_id', $carriers, null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('pickup_date', 'Pickup Date', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('pickup_date', '', ['id' => 'datepicker', 'class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('time_available', 'Time Available', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::select('time_available', $times, null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('close_time', 'Close Time', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::select('close_time', $times, null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>

            <p></p>

            <div class="form-group">
                {!! Form::submit('Request Pickup', ['class' => 'btn btn-primary col-md-2 form-control']) !!}
            </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>
<br>

@stop