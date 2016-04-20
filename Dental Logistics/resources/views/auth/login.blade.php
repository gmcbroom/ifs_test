<!-- resources/views/auth/register.blade.php -->
@extends('master')


@section('content')
<script type="text/javascript">
    $(window).load(function () {
        $('#loginModal').modal('show');
    });
</script>

<!--    include content     -->
<div class="container-fluid setBg">
    <div class="container text-center" >
        &nbsp;
        <!--login modal-->
        <div id="loginModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="text-center">
                            <a href="#"><img src="{{ asset('images/logo.gif') }}"></a>
                        </div>
                    </div>
                    <div class="modal-body">
                        @include('partials.list')
                        <form class="form col-md-12" role="form" method="POST" action="/auth/login">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group">
                                <input class="form-control input-lg" name="email" placeholder="Email" type="text" value="{{ old('email') }}">
                            </div>
                            <div class="form-group">
                                <input class="form-control input-lg" name="password" placeholder="Password" type="password" value="{{ old('password') }}">
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-lg btn-block">Sign In</button>
                                <div>
                                    <br>
                                    <span class="pull-right"><a href="/auth/terms"><b>No account - Register</b></a></span><br><span class="pull-right"><a href="/password/email"><b>Forgotten Password?</b></a></span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop