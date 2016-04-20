<div class="form-group">
    {!! Form::label('email', 'Email Address', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('email', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('title', 'Title', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6 ">
        {!! Form::select('title',
        ['Mr' => 'Mr', 'Mrs' => 'Mrs', 'Miss' => 'Miss', 'Ms' => 'Ms'],
        null,
        ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('first_name', 'First Name', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('first_name', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('last_name', 'Last Name', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('last_name', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('phone', 'Phone', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('phone', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('company', 'Practice', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('company', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('address1', 'Address line 1', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('address1', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('address2', 'Address line 2', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('address2', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('address3', 'Address line 3', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('address3', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('city', 'Town', ['class' => 'col-md-4 control-label col-md-offset-1']) !!}
    <div class="col-md-6">
        {!! Form::text('city', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('county', 'County', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('county', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('postcode', 'PostCode', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('postcode', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('mobile', 'Mobile Number', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('mobile', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('device_id', 'Mobile Notification ID', ['class' => 'col-md-4 col-md-offset-1 control-label']) !!}
    <div class="col-md-6">
        {!! Form::text('device_id', null, ['class' => 'form-control col-md-6']) !!}
    </div>
</div>

<div class="form-group">
    <div class="col-md-10 col-md-offset-1 alert-info text-center"><br>Mobile Notification ID required for notification messages<br><br></div>
</div>
