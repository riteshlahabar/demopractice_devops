@php
    $languages = collect($storeLanguages ?? []);
    $currentLanguage = $currentStoreLanguage ?? $languages->first();
@endphp

<li>
    <div class="dropdown theme-form-select store-topbar-language">
        <button class="btn dropdown-toggle" type="button" id="select-language" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <span class="store-india-flag" aria-hidden="true"></span>
            <span>{{ $currentLanguage?->name ?? 'English' }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end store-topbar-language-menu">
            @forelse($languages as $language)
                <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between {{ ($currentLanguage?->code === $language->code) ? 'active' : '' }}" href="{{ route('store.language', ['locale' => $language->code]) }}">
                        <span class="d-flex align-items-center gap-2">
                            <span class="store-india-flag" aria-hidden="true"></span>
                            <span>{{ $language->name }}</span>
                        </span>
                        @if($language->native_name && $language->native_name !== $language->name)
                            <small>{{ $language->native_name }}</small>
                        @endif
                    </a>
                </li>
            @empty
                <li><span class="dropdown-item active"><span class="store-india-flag" aria-hidden="true"></span> English</span></li>
            @endforelse
        </ul>
    </div>
</li>
<li>
    <div class="dropdown theme-form-select store-topbar-currency">
        <button class="btn dropdown-toggle" type="button" id="select-currency" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <span>INR</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end sm-dropdown-menu">
            <li><span class="dropdown-item active">INR</span></li>
        </ul>
    </div>
</li>