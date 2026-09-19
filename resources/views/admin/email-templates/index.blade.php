@extends('admin.layouts.app')
@section('title','Email Templates')
@section('content')
<div class="row">
@foreach($templates as $template)
<div class="col-md-6 col-xl-4"><div class="card"><div class="card-body"><div class="d-flex align-items-center gap-3"><div class="bg-primary-subtle text-primary rounded p-3"><i data-feather="mail"></i></div><div><h5 class="mb-1">{{ str($template)->replace('-',' ')->title() }}</h5><p class="text-muted mb-0">Fastkart responsive email layout</p></div></div><div class="mt-3"><a target="_blank" class="btn btn-primary" href="{{ route('admin.email-templates.show',$template) }}">Preview Template</a></div></div></div></div>
@endforeach
</div>
@endsection