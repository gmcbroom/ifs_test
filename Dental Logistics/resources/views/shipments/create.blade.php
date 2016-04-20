@extends('master')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-8 text">
            <h1>Create Shipment</h1>
            <hr/>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="error_pane" >
                @include('partials.list')
            </div>

            {!! Form::open(['method' => 'POST', 'url' => '/ship/create', 'class' => 'form-horizontal']) !!}

            <div class="form-group">
                {!! Form::label('patient', 'Patients Name', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('patient', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('order_number', 'Order Number', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('order_number', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('order_summary', 'Order Summary', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('order_summary', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('order_specifications', 'Order Specifications', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::textarea('order_specifications', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('lab_id', 'Laboratory', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::select('lab_id', $addresses, null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('pkg_length', 'Len(cms)', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('pkg_length', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('pkg_width', 'Width(cms)', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('pkg_width', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('pkg_height', 'Height(cms)', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('pkg_height', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('pkg_weight', 'Weight(kgs)', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('pkg_weight', null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('next_visit', 'Next Visit', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::text('next_visit', '', ['id' => 'datepicker', 'class' => 'form-control col-md-6']) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('service_level', 'Service Level', ['class' => 'col-md-4 control-label']) !!}
                <div class="col-md-6">
                    {!! Form::select('service_level', $service_levels, null, ['class' => 'form-control col-md-6']) !!}
                </div>
            </div>

            <p></p>

            <div class="form-group">
                {!! Form::submit('Add Shipment', ['class' => 'btn btn-primary col-md-2 form-control']) !!}
            </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>
<br>

@stop