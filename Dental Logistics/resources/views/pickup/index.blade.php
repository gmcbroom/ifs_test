<!-- resources/views/auth/register.blade.php -->
@extends('master')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-1">
            <div class="error_pane" >
                @include('partials.list')
            </div>
            <!-- Pickup Requests -->
            <div class="panel panel-default">
                <div class="panel-heading pull-right"><a href="/pickup/create" id="linkid"><h4>Create</h4></a></div>
                <div class="pull-right">{!! $pickups->render() !!}&nbsp;&nbsp;</div>
                <div class="panel-heading">
                    <h4>Pickup Request History</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped task-table">

                        <!-- Table Headings -->
                        <thead>
                        <th>Date</th>
                        <th>Carrier</th>
                        <th>Time Available</th>
                        <th>Close Time</th>
                        <th>Created At</th>
                        <th>Cancel</th>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            @if (count($pickups) > 0)
                            @foreach ($pickups as $pickup)
                            <tr>
                                <td class="table-text">
                                    <div>{{ $pickup->pickup_date }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $pickup->carrier->name }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $pickup->time_available }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $pickup->close_time }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $pickup->created_at }}</div>
                                </td>
                                <td>
                                    <form action="{{ url('/pickup/'.$pickup->id.'/delete') }}" method="POST">
                                        {!! csrf_field() !!}
                                        @if ($pickup->status == 'A')
                                        <button type="submit" class="btn btn-action">
                                            Cancel
                                        </button>
                                        @else
                                        {{ $pickup->cancelled_at }}
                                        @endif
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
