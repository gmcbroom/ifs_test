        <div class="form-group">
            {!! Form::Label('address_type', 'Type : ') !!}
            {!! Form::text('address_type', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('name', 'Name : ') !!}
            {!! Form::text('name', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('street1', 'Street : ') !!}
            {!! Form::text('street1', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('street2', '') !!}
            {!! Form::text('street2', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('city', 'Town : ') !!}
            {!! Form::text('city', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('county', 'County : ') !!}
            {!! Form::text('county', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('postcode', 'PostCode : ') !!}
            {!! Form::text('postcode', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('country', 'Country : ') !!}
            {!! Form::text('country', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('contact', 'Contact : ') !!}
            {!! Form::text('contact', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('email', 'Email Address : ') !!}
            {!! Form::email('email', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('phone', 'Phone : ') !!}
            {!! Form::text('phone', null, ['class' => 'form-control']) !!}
        </div>
    
        <div class="form-group">
            {!! Form::Label('residential', 'Residential : ') !!}
            {!! Form::text('residential', null, ['class' => 'form-control']) !!}
        </div>

    <div class="form-group">
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary form-control']) !!}
    </div>