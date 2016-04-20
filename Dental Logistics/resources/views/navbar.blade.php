<div class="navbar-bar"/>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="page-title">
            Demo Logistics
            @if (Auth::check())
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/ship/create">Book Shipment</a></li>
                @if (Auth::user()->admin)
                <li><a href="/ship">Shipment History</a></li>
                <li><a href="/pickup">Pickup Requests</a></li>
                <li><a href="/user">Admin</a></li>
                @else
                <li><a href="/ship">History</a></li>
                <li><a href="/pickup">Pickup Requests</a></li>
                <li><a href="/user/{{ Auth::user()->id }}/edit">Admin</a></li>
                @endif
                <li><a href="/auth/logout">Logout</a></li>
            </ul>
            @else
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/auth/login">Home</a></li>
            </ul>
            @endif
        </div>
    </div>
</nav>
