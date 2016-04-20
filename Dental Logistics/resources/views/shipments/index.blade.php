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
            @if (count($shipments) > 0)
            <div class="panel panel-default">
                <div class="pull-right">{!! $shipments->render() !!}&nbsp;&nbsp;</div>
                <div class="panel-heading">
                    <h4>Shipment History</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped task-table">

                        <!-- Table Headings -->
                        <thead>
                        <th>Date</th>
                        <th>Reference Number</th>
                        <th>Patients Name</th>
                        <th>Delivery Address</th>
                        <th>Shipment Ref</th>
                        <th>Ship Label</th>
                        <th>Returns Label</th>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            @foreach ($shipments as $shipment)
                            <tr>
                                <td class="table-text">
                                    <div>{{ $shipment->date_available }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $shipment->customer_reference }}</div>
                                </td>
                                <td class="table-text">
                                    @if ($shipment->order_id > 0)
                                    <div>{{ $shipment->order->patient_name }}</div>
                                    @else
                                    <div>Return Shipment</div>
                                    @endif
                                </td>
                                <td class="table-text">
                                    <div>{{ $shipment->consignee_company }}</div>
                                </td>
                                <td class="table-text">
                                    <div>{{ $shipment->consignmentno }}</div>
                                </td>
                                <td>
                                    @if ($shipment->order_id > 0)
                                    <form action="{{ url('ship/'.$shipment->id.'/label') }}" method="POST">
                                        {!! csrf_field() !!}

                                        <button type="submit" class="btn btn-action">
                                            Print
                                        </button>
                                    </form>
                                    @endif
                                </td>
                                <td>
                                    @if ($shipment->returns_label_printed == "N")
                                    <form action="{{ url('ship/'.$shipment->id.'/label/r') }}" method="POST">
                                        {!! csrf_field() !!}

                                        <button type="submit" class="btn btn-action">
                                            @if ($shipment->order_id > 0)
                                            Generate
                                            @else
                                            Print
                                            @endif
                                        </button>
                                    </form>
                                    @endif
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
