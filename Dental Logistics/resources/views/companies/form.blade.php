<div class="container col-sm-4">
        <div class="form-group">
            {!! Form::Label('display_name', 'Display Name : ') !!}
            {!! Form::text('display_name', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('address_id', 'Address : ') !!}
            {!! Form::text('address_id', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('logo', 'Logo Path : ') !!}
            {!! Form::text('logo', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('depot', 'Depot') !!}
            {!! Form::text('depot', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('status', 'Status : ') !!}
            {!! Form::text('status', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('account', 'Account : ') !!}
            {!! Form::text('account', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('fuelcap', 'Fuel Cap : ') !!}
            {!! Form::text('fuelcap', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('default_carrier', 'Default Carrier : ') !!}
            {!! Form::text('default_carrier', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('salesman', 'Salesman : ') !!}
            {!! Form::text('salesman', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('currency', 'Currency : ') !!}
            {!! Form::email('currency', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('weight_units', 'Weight Units : ') !!}
            {!! Form::text('weight_units', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('dim_units', 'Dim Units : ') !!}
            {!! Form::text('dim_units', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('carrier_pickup', 'Carrier Pickup : ') !!}
            {!! Form::text('carrier_pickup', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('show_price', 'Show Price : ') !!}
            {!! Form::text('show_price', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('vat_exempt', 'Vat Exempt : ') !!}
            {!! Form::text('vat_exempt', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('driver_label', 'Driver Label : ') !!}
            {!! Form::text('driver_label', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('customer_label', 'Customer Label : ') !!}
            {!! Form::text('customer_label', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('summary_label', 'Summary Label : ') !!}
            {!! Form::text('summary_label', null, ['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::Label('label_format', 'Label Format : ') !!}
            {!! Form::text('label_format', null, ['class' => 'form-control']) !!}
        </div>

    <div class="form-group">
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary form-control']) !!}
    </div>
</div>