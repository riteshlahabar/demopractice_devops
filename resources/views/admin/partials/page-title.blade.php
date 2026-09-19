<div class="page-title">
    <div class="row">
        <div class="col-sm-6"><h3>{{ $pageTitle ?? trim($__env->yieldContent('title')) ?: 'Admin' }}</h3></div>
        <div class="col-sm-6"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i data-feather="home"></i></a></li>@foreach(($breadcrumbs ?? []) as $breadcrumb)<li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">{{ $breadcrumb }}</li>@endforeach</ol></div>
    </div>
</div>