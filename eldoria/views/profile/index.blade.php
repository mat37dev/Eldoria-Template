@extends('layouts.app')

@section('title', __('theme::theme.profile.title'))

@section('content')
<div class="min-h-screen px-4 py-24">
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="text-center mb-8">
            <p class="section-eyebrow">✦ {{ __('theme::theme.profile.eyebrow') }} ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">{{ __('theme::theme.profile.title') }}</h1>
        </div>

        {{-- ======= IDENTITÉ ======= --}}
        <div class="card-eldoria p-8">
            <div class="flex items-start gap-4 mb-8 pb-8 border-b border-accent/10">
                <div x-data="{ editing: false }" class="flex-shrink-0">
                    <img src="{{ auth()->user()->getAvatar(64) }}" alt="{{ auth()->user()->name }}"
                         class="w-16 h-16 rounded-sm">

                    @if($canUploadAvatar || $hasAvatar)
                        <button type="button" @click="editing = !editing"
                                class="block mt-1 text-text-secondary hover:text-text-primary text-xs underline underline-offset-2">
                            {{ __('theme::theme.profile.edit') }}
                        </button>

                        <div x-show="editing" x-cloak class="mt-3 w-48 space-y-2">
                            @if($canUploadAvatar)
                                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                                    @csrf
                                    <label class="block text-text-secondary text-xs mb-1">{{ __('theme::theme.profile.avatar_title') }}</label>
                                    <input type="file" name="image" accept="image/png,image/jpeg,image/gif"
                                           class="w-full text-text-primary text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded-sm
                                                  file:border-0 file:bg-accent file:text-text-primary file:text-xs file:font-display file:uppercase">
                                    <p class="text-text-secondary text-xs mt-1">{{ __('theme::theme.profile.avatar_help') }}</p>
                                    @error('image')
                                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <button type="submit" class="btn-primary text-xs py-2 px-3 mt-2 w-full justify-center">
                                        {{ __('theme::theme.profile.avatar_upload_button') }}
                                    </button>
                                </form>
                            @endif

                            @if($hasAvatar)
                                <form method="POST" action="{{ route('profile.avatar.delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full text-center py-2 border border-accent/30 text-text-secondary hover:text-text-primary
                                                   text-xs font-display tracking-widest uppercase rounded-sm transition-colors">
                                        {{ __('theme::theme.profile.avatar_delete_button') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div x-data="{ editing: {{ $errors->has('name') ? 'true' : 'false' }} }">
                        <div x-show="!editing" class="font-display text-text-primary text-lg font-semibold flex items-center gap-2 flex-wrap">
                            {{ auth()->user()->name }}
                            @if(auth()->user()->role)
                                <span class="px-2 py-0.5 rounded-sm text-xs font-display uppercase tracking-wide"
                                      style="{{ auth()->user()->role->getBadgeStyle() }}">
                                    {{ auth()->user()->role->name }}
                                </span>
                            @endif
                            @if($canChangeName)
                                <button type="button" @click="editing = true" class="text-text-secondary hover:text-accent text-xs underline underline-offset-2">
                                    {{ __('theme::theme.profile.edit') }}
                                </button>
                            @endif
                        </div>

                        @if($canChangeName)
                            <form x-show="editing" x-cloak method="POST" action="{{ route('profile.name') }}" class="flex flex-wrap items-center gap-2 mt-1">
                                @csrf
                                <label class="sr-only" for="nameInput">{{ __('theme::theme.profile.name_label') }}</label>
                                <input type="text" id="nameInput" name="name" maxlength="25" value="{{ old('name', auth()->user()->name) }}"
                                       class="bg-bg-secondary border border-accent/20 rounded-sm px-3 py-1.5 text-text-primary text-sm min-h-[36px]">
                                <button type="submit" class="btn-primary text-xs py-1.5 px-3 min-h-[36px]">{{ __('theme::theme.profile.save') }}</button>
                                <button type="button" @click="editing = false" class="text-text-secondary hover:text-text-primary text-xs">
                                    {{ __('theme::theme.customizer.cancel') }}
                                </button>
                            </form>
                            @error('name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div x-data="{ editing: {{ $errors->has('email') ? 'true' : 'false' }} }" class="mt-1">
                        <div x-show="!editing" class="flex items-center gap-2 flex-wrap">
                            <span class="text-text-secondary text-sm">{{ auth()->user()->email }}</span>
                            <button type="button" @click="editing = true" class="text-text-secondary hover:text-accent text-xs underline underline-offset-2">
                                {{ __('theme::theme.profile.edit') }}
                            </button>
                        </div>

                        <form x-show="editing" x-cloak method="POST" action="{{ route('profile.email') }}" class="space-y-2 mt-1 max-w-xs">
                            @csrf
                            <label class="sr-only" for="emailInput">{{ __('theme::theme.auth.email') }}</label>
                            <input type="email" id="emailInput" name="email" maxlength="50" value="{{ old('email', auth()->user()->email) }}"
                                   class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-3 py-1.5 text-text-primary text-sm min-h-[36px]">
                            @error('email')
                                <p class="text-red-400 text-xs">{{ $message }}</p>
                            @enderror

                            @unless(oauth_login())
                                <label class="sr-only" for="emailConfirmPass">{{ __('theme::theme.profile.email_confirm_password_label') }}</label>
                                <input type="password" id="emailConfirmPass" name="email_confirm_pass"
                                       placeholder="{{ __('theme::theme.profile.email_confirm_password_label') }}"
                                       class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-3 py-1.5 text-text-primary text-sm min-h-[36px]">
                                @error('email_confirm_pass')
                                    <p class="text-red-400 text-xs">{{ $message }}</p>
                                @enderror
                            @endunless

                            <div class="flex items-center gap-2">
                                <button type="submit" class="btn-primary text-xs py-1.5 px-3 min-h-[36px]">{{ __('theme::theme.profile.save') }}</button>
                                <button type="button" @click="editing = false" class="text-text-secondary hover:text-text-primary text-xs">
                                    {{ __('theme::theme.customizer.cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($canVerifyEmail)
                        <div class="mt-3 flex items-center gap-3 flex-wrap">
                            <span class="text-red-400 text-xs">{{ __('theme::theme.profile.verify_email_text') }}</span>
                            <form method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="text-accent-secondary hover:text-accent text-xs underline underline-offset-2">
                                    {{ __('theme::theme.profile.verify_email_button') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.member_since') }}</span>
                    <span class="text-text-primary">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.last_login_label') }}</span>
                    <span class="text-text-primary">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y à H:i') : __('theme::theme.profile.last_login_never') }}</span>
                </div>
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.balance_label') }}</span>
                    <span class="text-accent font-display font-bold">{{ format_money(auth()->user()->money) }}</span>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-accent/10">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-accent text-text-primary font-display text-sm tracking-widest uppercase
                                   rounded-sm hover:bg-accent/90 transition-all min-h-[48px]">
                        {{ __('theme::theme.profile.logout') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- ======= SÉCURITÉ ======= --}}
        <div class="card-eldoria p-8">
            <h2 class="font-display text-text-primary text-sm tracking-widest uppercase mb-6">{{ __('theme::theme.profile.password_title') }}</h2>

            @unless(oauth_login())
                <form method="POST" action="{{ route('profile.password') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="passwordConfirmPass">
                            {{ __('theme::theme.profile.password_current_label') }}
                        </label>
                        <input type="password" id="passwordConfirmPass" name="password_confirm_pass"
                               class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                        @error('password_confirm_pass')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="passwordNew">
                            {{ __('theme::theme.profile.password_new_label') }}
                        </label>
                        <input type="password" id="passwordNew" name="password"
                               class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                        @error('password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="passwordConfirmation">
                            {{ __('theme::theme.profile.password_confirm_label') }}
                        </label>
                        <input type="password" id="passwordConfirmation" name="password_confirmation"
                               class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                    </div>
                    <div class="sm:col-span-3">
                        <button type="submit" class="btn-primary min-h-[48px]">{{ __('theme::theme.profile.save') }}</button>
                    </div>
                </form>
            @endunless

            <div class="mt-6 pt-6 border-t border-accent/10 flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <div class="font-display text-text-primary text-sm tracking-widest uppercase mb-1">{{ __('theme::theme.profile.twofa_title') }}</div>
                    <p class="text-text-secondary text-xs">
                        {{ auth()->user()->hasTwoFactorAuth() ? __('theme::theme.profile.twofa_enabled') : __('theme::theme.profile.twofa_disabled') }}
                    </p>
                </div>
                <a href="{{ route('profile.2fa.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2 min-h-[40px] border border-accent/40 text-text-secondary hover:text-text-primary hover:border-accent
                          text-xs font-display tracking-widest uppercase rounded-sm transition-all">
                    {{ __('theme::theme.profile.twofa_manage_button') }}
                </a>
            </div>
        </div>

        {{-- ======= DISCORD ======= --}}
        @if($enableDiscordLink)
        <div class="card-eldoria p-8 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="font-display text-text-primary text-sm tracking-widest uppercase mb-1">{{ __('theme::theme.profile.discord_title') }}</h2>
                <p class="text-text-secondary text-xs">
                    {{ $discordAccount ? __('theme::theme.profile.discord_linked', ['name' => $discordAccount->name]) : __('theme::theme.profile.discord_not_linked') }}
                </p>
            </div>

            @if($discordAccount)
                <form method="POST" action="{{ route('profile.discord.unlink') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 min-h-[40px] border border-accent/40 text-text-secondary hover:text-text-primary hover:border-accent
                                   text-xs font-display tracking-widest uppercase rounded-sm transition-all">
                        {{ __('theme::theme.profile.discord_unlink_button') }}
                    </button>
                </form>
            @else
                <a href="{{ route('profile.discord.link') }}" class="btn-primary text-xs py-2 px-4 min-h-[40px]">
                    {{ __('theme::theme.profile.discord_link_button') }}
                </a>
            @endif
        </div>
        @endif

        {{-- ======= SKIN 3D ======= --}}
        <div class="card-eldoria p-8">
            @php
                $skinIdentifier = game()->id() === 'mc-offline'
                    ? auth()->user()->name
                    : (auth()->user()->game_id ?? 'c06f8906-4c8a-4911-9c29-ea1dbd1aab82');
            @endphp
            <h2 class="font-display text-text-primary text-sm tracking-widest uppercase mb-6 text-center">
                {{ __('theme::theme.profile.skin_3d_title') }}
            </h2>
            <div class="flex justify-center">
                <canvas id="skin-viewer-canvas"
                        data-skin-url="https://mc-heads.net/skin/{{ $skinIdentifier }}"
                        width="300" height="400"></canvas>
            </div>
        </div>

        {{-- ======= TRANSFERT DE RUBIS ======= --}}
        @if(setting('users.money_transfer'))
        <div class="card-eldoria p-8">
            <h2 class="font-display text-text-primary text-sm tracking-widest uppercase mb-6">{{ __('theme::theme.profile.transfer_title') }}</h2>
            <form method="POST" action="{{ route('profile.transfer-money') }}" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-4 items-start">
                @csrf
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="transferName">
                        {{ __('theme::theme.profile.transfer_recipient_label') }}
                    </label>
                    <input type="text" id="transferName" name="name" placeholder="{{ __('theme::theme.profile.transfer_recipient_placeholder') }}"
                           class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                    @error('name')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="transferMoney">
                        {{ __('theme::theme.profile.transfer_amount_label') }}
                    </label>
                    <input type="number" id="transferMoney" name="money" min="0.01" step="0.01"
                           class="w-full bg-bg-secondary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                    @error('money')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary min-h-[48px] mt-7">{{ __('theme::theme.profile.transfer_submit') }}</button>
            </form>
        </div>
        @endif

        {{-- ======= STATISTIQUES ======= --}}
        <?php
            $statsVotes = plugins()->isEnabled('vote')
                ? \Azuriom\Plugin\Vote\Models\Vote::where('user_id', auth()->id())
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count()
                : null;

            $statsTickets = plugins()->isEnabled('support')
                ? \Azuriom\Plugin\Support\Models\Ticket::where('author_id', auth()->id())->count()
                : null;

            $statsSpent = plugins()->isEnabled('shop')
                ? \Azuriom\Plugin\Shop\Models\Payment::where('user_id', auth()->id())->where('status', 'completed')->sum('price')
                : null;
        ?>
        @if($statsVotes !== null || $statsTickets !== null || $statsSpent !== null)
        <div class="card-eldoria p-8">
            <h2 class="font-display text-text-primary text-sm tracking-widest uppercase mb-6">{{ __('theme::theme.profile.stats_title') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                @if($statsVotes !== null)
                    <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                        <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.stats_votes_month') }}</span>
                        <span class="text-text-primary font-display font-bold text-xl">{{ $statsVotes }}</span>
                    </div>
                @endif
                @if($statsTickets !== null)
                    <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                        <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.stats_tickets') }}</span>
                        <span class="text-text-primary font-display font-bold text-xl">{{ $statsTickets }}</span>
                    </div>
                @endif
                @if($statsSpent !== null)
                    <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                        <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.stats_spent') }}</span>
                        <span class="text-text-primary font-display font-bold text-xl">{{ format_money($statsSpent) }}</span>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ======= ZONE DANGEREUSE ======= --}}
        @if($canDelete)
        <div class="p-6 border-2 border-dashed border-red-500/30 rounded-xl flex items-center justify-between gap-4 flex-wrap">
            <div>
                <div class="font-display text-red-700 text-xs tracking-widest uppercase mb-1">{{ __('theme::theme.profile.delete_title') }}</div>
                <p class="text-text-secondary text-xs">{{ __('theme::theme.profile.delete_warning') }}</p>
            </div>
            <a href="{{ route('profile.delete.index') }}"
               class="inline-flex items-center justify-center px-4 py-2 min-h-[40px] border border-red-500/40 text-red-700 hover:bg-red-500/10
                      text-xs font-display tracking-widest uppercase rounded-sm transition-all">
                {{ __('theme::theme.profile.delete_button') }}
            </a>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="{{ theme_asset('dist/profile.js') }}" defer></script>
@endpush
