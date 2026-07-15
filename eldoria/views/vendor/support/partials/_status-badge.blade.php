@php
    $status = $ticket->status();
    $statusClasses = match($status) {
        'open' => 'bg-accent/10 border-accent/40 text-accent',
        'replied' => 'bg-accent-secondary/10 border-accent-secondary/40 text-accent-secondary',
        'closed' => 'bg-bg-primary border-text-secondary/30 text-text-secondary',
        default => 'bg-bg-primary border-text-secondary/30 text-text-secondary',
    };
@endphp
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs border {{ $statusClasses }}">
    {{ $ticket->statusMessage() }}
</span>
