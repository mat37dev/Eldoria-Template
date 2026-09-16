<div class="card-eldoria overflow-hidden">
    <a href="{{ route('changelog.index') }}"
       class="flex items-center justify-between px-4 py-3 text-sm border-b border-accent/10 last:border-b-0 transition-colors
              {{ $category === null ? 'bg-accent/10 text-accent font-semibold' : 'text-text-secondary hover:text-text-primary' }}">
        {{ trans('changelog::messages.all') }}
        <span class="text-xs font-mono">{{ $totalUpdates }}</span>
    </a>
    @foreach($categories as $cat)
        <a href="{{ route('changelog.categories.show', $cat) }}"
           class="flex items-center justify-between px-4 py-3 text-sm border-b border-accent/10 last:border-b-0 transition-colors
                  {{ $cat->is($category) ? 'bg-accent/10 text-accent font-semibold' : 'text-text-secondary hover:text-text-primary' }}">
            {{ $cat->name }}
            <span class="text-xs font-mono">{{ $cat->updates->count() }}</span>
        </a>
    @endforeach
</div>
