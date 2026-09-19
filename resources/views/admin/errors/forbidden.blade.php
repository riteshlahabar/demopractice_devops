@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i data-feather="lock" style="width:48px;height:48px;" class="text-danger mb-3"></i>
        <h4 class="mb-2">You do not have permission for this page or action.</h4>
        <p class="text-muted mb-4">Ask a Super Admin to give your role access to this section.</p>
        @if($homeUrl)
            <a href="{{ $homeUrl }}" class="btn btn-primary">Go to my first page</a>
        @else
            <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">@csrf<button class="btn btn-outline-secondary" type="submit">Log out</button></form>
        @endif
    </div>
</div>
@endsection
