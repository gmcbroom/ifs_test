<!-- resources/views/auth/register.blade.php -->
@extends('master')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="error_pane" >
                @include('partials.list')
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">Terms & Conditions</div>
                <div class="panel-body">

                    <form class="form-horizontal" role="form" method="POST" action="/auth/terms">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group text-center">
                            <p>
                                Terms & Conditions
                            </p>
                        </div>

                        <div class="form-group text-center">
                            <div>
                                <button type="submit" id="accept" name="action" value="accept" class="btn btn-primary">
                                    Accept
                                </button>
                                <button type="submit" id="reject" name="action" value="reject" class="btn btn-primary">
                                    Reject
                                </button>
                            </div>
                        </div>
                        {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>
</div>

@stop
