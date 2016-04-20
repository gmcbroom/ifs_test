@if (count($errors) > 0)
<div class = "alert alert-danger">
    <strong>Error :
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
        </ul>
    </strong>
</div>
@endif
@if (isset($message))
@if ($message > '')
<div class = "alert alert-info text-center">
    <h2>{{ $message }}</h2>
    @if (isset($info))
    @if ($info > '')
    <h3>{{ $info }}</h3>
    @endif
    @endif
</div>
@endif
@endif