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
                <div class="panel-heading">Register</div>
                <div class="panel-body">

                    {!! Form::open(['method' => 'POST', 'url' => '/auth/register']) !!}

                    @include('partials.userform',['submitButton' => 'Register'])

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-5">
                            {{Form::submit('Register',array('class'=>'btn btn-primary','name'=>'action','value'=>'register'))}}
                            {{Form::submit('Cancel',array('class'=>'btn btn-primary','name'=>'action','value'=>'cancel'))}}
                        </div>
                    </div>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>
</div>

@stop
