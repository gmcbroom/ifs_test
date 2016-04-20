<!-- resources/views/auth/register.blade.php -->
@extends('master')

@section('content')

<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-danger">
                <div class="panel-heading"><h3>Email Verification</h3></div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <h3 class="error-message">Invalid Token</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
