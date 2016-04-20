<!-- resources/views/auth/register.blade.php -->
@extends('master')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-1">
            <div class="error_pane" >
                @include('partials.list')
            </div>
            <!-- Users -->
            @if (count($users) > 0)
            <div class="panel panel-default">
                <div class="panel-heading">
                    Users
                </div>

                <div class="panel-body">
                    <table class="table table-striped task-table">

                        <!-- Table Headings -->
                        <thead>
                        <th>Company</th>
                        <th>Name</th>
                        <th>City</th>
                        <th>County</th>
                        <th>Status</th>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                <td class="table-text">
                                    <div>{{ $user->company }}<br>{{ $user->phone }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $user->title . " " . $user->first_name . " " . $user->last_name }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $user->city }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $user->county }}</div>
                                </td>
                                <td>
                                    @if ($user->admin)
                                    <form action="{{ url('user/'.$user->id).'/status' }}" method="POST">
                                        {!! csrf_field() !!}

                                        <button type="submit" class="btn btn-action">
                                            @if ($user->active)
                                            <div>De-Activate</div>
                                            @else
                                            <div>Activate</div>
                                            @endif
                                        </button>
                                    </form>
                                    @else
                                    @if ($user->active)
                                    <div>Active</div>
                                    @else
                                    <div>Non Active</div>
                                    @endif
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ url('user/'.$user->id).'/edit' }}" method="GET">
                                        {!! csrf_field() !!}

                                        <button type="submit" class="btn btn-action">
                                            Edit
                                        </button>
                                    </form>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@stop
