@extends('layouts.app')

@section('title', trans('shop::messages.profile.payments'))

@php
    // Mappe les couleurs Bootstrap renvoyées par Payment::statusColor() /
    // Subscription::statusColor() (success/warning/danger/secondary) vers le
    // système de badges Eldoria, même palette que support::partials._status-badge.
    $statusBadgeClass = fn (string $color) => match ($color) {
        'success' => 'bg-accent/10 border-accent/40 text-accent',
        'warning' => 'bg-accent-secondary/10 border-accent-secondary/40 text-accent-secondary',
        'danger' => 'bg-red-500/10 border-red-500/40 text-red-600',
        default => 'bg-bg-primary border-text-secondary/30 text-text-secondary',
    };
@endphp

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="section-eyebrow">✦ {{ __('theme::theme.shop.hero_eyebrow') }} ✦</p>
        <h1 class="section-title">{{ trans('shop::messages.profile.payments') }}</h1>
    </div>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="card-eldoria p-6 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-text-secondary text-xs uppercase tracking-widest border-b border-accent/10">
                        <th class="py-3 pr-4">#</th>
                        <th class="py-3 pr-4">{{ trans('shop::messages.fields.price') }}</th>
                        <th class="py-3 pr-4">{{ trans('messages.fields.type') }}</th>
                        <th class="py-3 pr-4">{{ trans('messages.fields.status') }}</th>
                        <th class="py-3 pr-4">{{ trans('shop::messages.fields.payment_id') }}</th>
                        <th class="py-3">{{ trans('messages.fields.date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-b border-accent/10 last:border-b-0">
                            <td class="py-3 pr-4 text-text-primary">{{ $payment->id }}</td>
                            <td class="py-3 pr-4 text-text-primary font-display font-bold">{{ $payment->formatPrice() }}</td>
                            <td class="py-3 pr-4 text-text-secondary">{{ $payment->getTypeName() }}</td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs border {{ $statusBadgeClass($payment->statusColor()) }}">
                                    {{ trans('shop::admin.payments.status.'.$payment->status) }}
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-text-secondary font-mono text-xs">{{ $payment->transaction_id ?? trans('messages.unknown') }}</td>
                            <td class="py-3 text-text-secondary">{{ format_date($payment->created_at, true) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-text-secondary">{{ trans('shop::messages.no_products') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(! $subscriptions->isEmpty())
            <div class="card-eldoria p-6 overflow-x-auto">
                <h2 class="font-display text-text-primary text-sm tracking-widest uppercase mb-4">{{ trans('shop::messages.profile.subscriptions') }}</h2>
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-text-secondary text-xs uppercase tracking-widest border-b border-accent/10">
                            <th class="py-3 pr-4">#</th>
                            <th class="py-3 pr-4">{{ trans('shop::messages.fields.price') }}</th>
                            <th class="py-3 pr-4">{{ trans('shop::messages.fields.package') }}</th>
                            <th class="py-3 pr-4">{{ trans('messages.fields.status') }}</th>
                            <th class="py-3 pr-4">{{ trans('messages.fields.date') }}</th>
                            <th class="py-3 pr-4">{{ trans('shop::messages.fields.renewal_date') }}</th>
                            <th class="py-3">{{ trans('messages.fields.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            <tr class="border-b border-accent/10 last:border-b-0">
                                <td class="py-3 pr-4 text-text-primary">{{ $subscription->id }}</td>
                                <td class="py-3 pr-4 text-text-primary font-display font-bold">{{ $subscription->formatPrice() }}</td>
                                <td class="py-3 pr-4 text-text-secondary">{{ $subscription->package?->name ?? trans('messages.unknown') }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs border {{ $statusBadgeClass($subscription->statusColor()) }}">
                                        {{ trans('shop::admin.subscriptions.status.'.$subscription->status) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-text-secondary">{{ format_date($subscription->created_at) }}</td>
                                <td class="py-3 pr-4 text-text-secondary">{{ $subscription->ends_at !== null ? format_date($subscription->ends_at) : '—' }}</td>
                                <td class="py-3">
                                    @if($subscription->isActive() && ! $subscription->isCanceled())
                                        <form action="{{ route('shop.subscriptions.destroy', $subscription) }}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:underline text-xs">
                                                {{ trans('messages.actions.cancel') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
