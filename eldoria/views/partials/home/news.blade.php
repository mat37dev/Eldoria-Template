@php $latestPosts = \Azuriom\Models\Post::published()->with('author')->latest('published_at')->take(3)->get(); @endphp
@if($latestPosts->isNotEmpty())
<section class="py-24 px-4 max-w-7xl mx-auto {{ $sectionData['visible'] ? '' : 'hidden' }}"
         data-section-key="news" data-aos="fade-up">
    <h2 class="section-title">{{ __('theme::theme.home.news_title') }}</h2>
    <p class="section-subtitle">{{ __('theme::theme.home.news_subtitle') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        @foreach($latestPosts as $post)
        <div class="card-eldoria overflow-hidden flex flex-col group" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
            @if($post->hasImage())
                <div class="overflow-hidden">
                    <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}"
                         class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
            @endif
            <div class="p-6 flex flex-col flex-1">
                <h3 class="font-display text-text-primary font-semibold mb-2">
                    <a href="{{ route('posts.show', $post) }}" class="hover:text-accent transition-colors">{{ $post->title }}</a>
                </h3>
                <p class="text-text-secondary text-sm mb-4 flex-1 line-clamp-2">{{ Str::limit(strip_tags($post->content), 120) }}</p>
                <a href="{{ route('posts.show', $post) }}" class="btn-primary text-xs py-2 px-4 self-start">
                    {{ __('theme::theme.posts.read_more') }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center">
        <a href="{{ route('posts.index') }}" class="btn-primary">
            {{ __('theme::theme.home.news_see_all') }}
        </a>
    </div>
</section>
@endif
