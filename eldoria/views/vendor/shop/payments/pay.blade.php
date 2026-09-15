@extends('layouts.app')

@section('title', trans('shop::messages.payment.title'))

@push('footer-scripts')
    <script>
        document.querySelectorAll('.payment-method').forEach(function (el) {
            el.addEventListener('click', function (ev) {
                ev.preventDefault();

                const form = document.getElementById('submitForm');
                form.action = el.href;
                form.submit();
            });
        });
    </script>
@endpush

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="section-eyebrow">✦ {{ __('theme::theme.shop.hero_eyebrow') }} ✦</p>
        <h1 class="section-title">{{ trans('shop::messages.payment.title') }}</h1>
    </div>

    <div class="max-w-3xl mx-auto">
        @if($gateways->isEmpty())
            <div class="card-eldoria p-6 text-center text-text-secondary text-sm">
                {{ trans('shop::messages.payment.empty') }}
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach($gateways as $gateway)
                    <a href="{{ isset($route) ? $route($gateway->type) : route('shop.payments.pay', $gateway->type) }}"
                       class="payment-method card-eldoria p-6 flex items-center justify-center min-h-[96px] hover:-translate-y-1 transition-transform duration-200">
                        <img src="{{ $gateway->paymentMethod()->image() }}" alt="{{ $gateway->name }}" class="max-h-10 w-auto">
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <form method="POST" id="submitForm">
        @csrf
    </form>
</div>
@endsection
