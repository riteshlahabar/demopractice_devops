@php
    // Two columns built from the section's items, in Sort Order. With the
    // "Video + Text" layout the first item is the video and the second is the
    // text; with "Two Videos" both columns are players.
    $videoItems = collect($videoItems ?? []);
    $videoLayout = (string) ($videoLayout ?? 'two_videos');
    $videoColumns = $videoItems->take(2)->values();
    $videoPoster = static function ($item): ?string {
        $path = trim((string) ($item->image_path ?? ''));

        if ($path === '') {
            return null;
        }

        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    };
@endphp

@if ($videoColumns->isNotEmpty())
    <section class="video-section section-b-space" id="home-section-{{ $videoSection->section_key }}">
        <div class="container-fluid-lg">
            @if (filled($videoSection->title) || filled($videoSection->subtitle))
                <div class="title">
                    @if (filled($videoSection->title))
                        <h2>{{ storefront_public_t($videoSection->title, 'homepage_section') }}</h2>
                    @endif
                    <span class="title-leaf">
                        <svg class="icon-width">
                            <use xlink:href="{{ asset('fastkart-store/svg/leaf.svg') }}#leaf"></use>
                        </svg>
                    </span>
                    @if (filled($videoSection->subtitle))
                        <p>{{ storefront_public_t($videoSection->subtitle, 'homepage_section') }}</p>
                    @endif
                </div>
            @endif

            <div class="row g-4">
                @foreach ($videoColumns as $videoItem)
                    @php
                        $isTextColumn = $videoLayout === 'video_text' && $loop->index === 1;
                        $videoSource = $isTextColumn ? null : $videoItem->videoSource();
                        $poster = $videoPoster($videoItem);
                    @endphp

                    <div class="col-lg-6 col-12">
                        @if ($isTextColumn)
                            <div class="video-text-box h-100">
                                @if (filled($videoItem->subtitle))
                                    <h4>{{ storefront_public_t($videoItem->subtitle, 'homepage_entry') }}</h4>
                                @endif
                                @if (filled($videoItem->title))
                                    <h3>{{ storefront_public_t($videoItem->title, 'homepage_entry') }}</h3>
                                @endif
                                @if (filled($videoItem->description))
                                    <p>{{ storefront_public_t($videoItem->description, 'homepage_entry') }}</p>
                                @endif
                                @if (filled($videoItem->button_text) && filled($videoItem->button_url))
                                    <a href="{{ $videoItem->button_url }}" class="btn theme-bg-color text-white btn-md fw-bold mt-3">{{ storefront_public_t($videoItem->button_text, 'homepage_button') }}</a>
                                @endif
                            </div>
                        @elseif ($videoSource)
                            <div class="video-box">
                                @if ($videoSource['mode'] === 'embed')
                                    <iframe src="{{ $videoSource['src'] }}{{ $videoItem->video_autoplay ? '?autoplay=1&mute=1&loop=1' : '' }}"
                                        title="{{ $videoItem->title ?: $videoSection->title }}" loading="lazy" allowfullscreen
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                @else
                                    <video controls playsinline preload="metadata"
                                        @if($poster) poster="{{ $poster }}" @endif
                                        @if($videoItem->video_autoplay) autoplay muted loop @endif>
                                        <source src="{{ $videoSource['src'] }}">
                                    </video>
                                @endif

                                @if (filled($videoItem->title))
                                    <div class="video-caption">
                                        <h4>{{ storefront_public_t($videoItem->title, 'homepage_entry') }}</h4>
                                        @if (filled($videoItem->subtitle))
                                            <p>{{ storefront_public_t($videoItem->subtitle, 'homepage_entry') }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
