@extends('admin.layouts.app')
@section('title', $pageTitle)
@section('content')
@php($canEdit ??= false)
<div class="row admin-form-row">
    <div class="col-12">
        <div class="card admin-form-card">
            <div class="card-body pt-3">
                <form method="POST" action="{{ route('admin.app-languages.update') }}" class="theme-form theme-form-2 mega-form">
                    @csrf
                    @method('PUT')

                    <div class="card-header-2"><h5>{{ $pageTitle }}</h5></div>
                    <p class="text-muted small">Choose which languages each app shows in its language list. Every active language in <strong>Languages</strong> is still translated, so switching one on here works straight away. English is always on.</p>

                    @if($languages === [])
                        <div class="alert alert-info mb-0">No active languages. Add or activate one in Translation &rarr; Languages.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 app-language-table">
                                <thead>
                                    <tr>
                                        <th>Language</th>
                                        @foreach($apps as $appKey => $appLabel)
                                            <th class="text-center">{{ $appLabel }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($languages as $language)
                                        <tr>
                                            <td>
                                                <strong>{{ $language['name'] }}</strong>
                                                @if($language['native_name'] !== '')<span class="text-muted ms-1">{{ $language['native_name'] }}</span>@endif
                                                <span class="badge badge-light-primary ms-1">{{ $language['code'] }}</span>
                                                @if($language['is_default'])<span class="badge badge-light-success ms-1">Default</span>@endif
                                            </td>
                                            @foreach($apps as $appKey => $appLabel)
                                                @php($id = 'appLang-'.$appKey.'-'.$language['code'])
                                                <td class="text-center">
                                                    <input class="checkbox_animated m-0" type="checkbox" id="{{ $id }}" name="apps[{{ $appKey }}][]" value="{{ $language['code'] }}" aria-label="{{ $language['name'] }} in {{ $appLabel }}"
                                                        @checked($language['apps'][$appKey] ?? true) @disabled($language['is_default'] || ! $canEdit)>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($canEdit)
                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-primary" type="submit"><i class="iconoir-check-circle me-1"></i>Save App Languages</button>
                            </div>
                        @endif
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
