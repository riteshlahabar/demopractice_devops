@php
    $languages = collect($storeLanguages ?? []);
    $currentLanguage = $currentStoreLanguage ?? $languages->first();
@endphp

<div class="language-box-2 dropdown d-xl-flex d-none">
    <button class="btn language-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-globe"></i>
        <span>{{ $currentLanguage?->name ?? 'English' }}</span>
    </button>
    <ul class="dropdown-menu language-dropdown">
        @forelse($languages as $language)
            <li>
                <a class="dropdown-item d-flex justify-content-between align-items-center {{ ($currentLanguage?->code === $language->code) ? 'active' : '' }}" href="{{ route('store.language', ['locale' => $language->code]) }}">
                    <span>{{ $language->name }}</span>
                    @if($language->native_name && $language->native_name !== $language->name)
                        <small>{{ $language->native_name }}</small>
                    @endif
                </a>
            </li>
        @empty
            <li><span class="dropdown-item active">English</span></li>
        @endforelse
    </ul>
</div>