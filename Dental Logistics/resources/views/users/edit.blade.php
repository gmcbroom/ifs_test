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
                @if (Auth::check())
                <div class="panel-heading">User Details</div>
                @else
                <div class="panel-heading">Register</div>
                @endif
                <div class="panel-body">
                    {!! Form::model($user, ['method' => 'PATCH', 'action' => ['UserController@update', $user->id]]) !!}

                    @include('partials.userform',['submitButton' => 'Save']);

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@stop
