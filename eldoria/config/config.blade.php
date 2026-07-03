@extends('admin.layouts.admin')

@section('title', 'Eldoria')

@section('content')
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Configuration du thème Eldoria</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.themes.config', $theme) }}" method="POST">
                    @csrf

                    <h6 class="mb-3">Couleurs</h6>

                    <div class="mb-3">
                        <label for="colorAccentInput" class="form-label">Accent principal</label>
                        <input type="color" class="form-control form-control-color @error('color_accent') is-invalid @enderror"
                               id="colorAccentInput" name="color_accent"
                               value="{{ old('color_accent', theme_config('color_accent')) }}">
                        @error('color_accent')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="colorAccentSecondaryInput" class="form-label">Accent secondaire</label>
                        <input type="color" class="form-control form-control-color @error('color_accent_secondary') is-invalid @enderror"
                               id="colorAccentSecondaryInput" name="color_accent_secondary"
                               value="{{ old('color_accent_secondary', theme_config('color_accent_secondary')) }}">
                        @error('color_accent_secondary')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <h6 class="mb-3">Contenu</h6>

                    <div class="mb-3">
                        <label for="heroSloganInput" class="form-label">Slogan du hero</label>
                        <textarea class="form-control @error('hero_slogan') is-invalid @enderror"
                                  id="heroSloganInput" name="hero_slogan" rows="3">{{ old('hero_slogan', theme_config('hero_slogan')) }}</textarea>
                        @error('hero_slogan')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="heroImageInput" class="form-label">Image du hero (URL)</label>
                        <input type="text" class="form-control @error('hero_image') is-invalid @enderror"
                               id="heroImageInput" name="hero_image" placeholder="https://..."
                               value="{{ old('hero_image', theme_config('hero_image')) }}">
                        <div class="form-text">Laisser vide pour utiliser l'image par défaut du thème.</div>
                        @error('hero_image')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <h6 class="mb-3">Équipe / Staff</h6>
                    <p class="text-muted small mb-3">Jusqu'à 8 membres. Le pseudo doit être un pseudo Minecraft valide (avatar via minotar.net).</p>

                    @for ($i = 1; $i <= 8; $i++)
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="staff{{ $i }}NameInput" class="form-label">Pseudo #{{ $i }}</label>
                            <input type="text" class="form-control @error("staff_{$i}_name") is-invalid @enderror"
                                   id="staff{{ $i }}NameInput" name="staff_{{ $i }}_name" placeholder="Pseudo Minecraft"
                                   value="{{ old("staff_{$i}_name", theme_config("staff_{$i}_name")) }}">
                            @error("staff_{$i}_name")
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="staff{{ $i }}RoleInput" class="form-label">Rôle #{{ $i }}</label>
                            <input type="text" class="form-control @error("staff_{$i}_role") is-invalid @enderror"
                                   id="staff{{ $i }}RoleInput" name="staff_{{ $i }}_role" placeholder="Ex: Fondateur"
                                   value="{{ old("staff_{$i}_role", theme_config("staff_{$i}_role")) }}">
                            @error("staff_{$i}_role")
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-12 mt-2">
                            <label for="staff{{ $i }}LinkInput" class="form-label">Lien (Discord, Twitter/X...) #{{ $i }} — optionnel</label>
                            <input type="url" class="form-control @error("staff_{$i}_link") is-invalid @enderror"
                                   id="staff{{ $i }}LinkInput" name="staff_{{ $i }}_link" placeholder="https://..."
                                   value="{{ old("staff_{$i}_link", theme_config("staff_{$i}_link")) }}">
                            @error("staff_{$i}_link")
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                    @endfor

                    <h6 class="mb-3">Médias &amp; communauté</h6>

                    <div class="mb-3">
                        <label for="trailerUrlInput" class="form-label">Trailer YouTube (URL)</label>
                        <input type="url" class="form-control @error('trailer_url') is-invalid @enderror"
                               id="trailerUrlInput" name="trailer_url" placeholder="https://youtu.be/..."
                               value="{{ old('trailer_url', theme_config('trailer_url')) }}">
                        <small class="form-text text-muted">Affiché dans une section dédiée sur la page d'accueil.</small>
                        @error('trailer_url')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="hero_video_enabled" value="0">
                        <input type="checkbox" class="form-check-input" role="switch"
                               id="heroVideoEnabledInput" name="hero_video_enabled" value="1"
                               {{ old('hero_video_enabled', theme_config('hero_video_enabled')) === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="heroVideoEnabledInput">Utiliser le trailer en fond du hero</label>
                    </div>

                    <div class="mb-4">
                        <label for="discordServerIdInput" class="form-label">ID du serveur Discord (widget)</label>
                        <input type="text" class="form-control @error('discord_server_id') is-invalid @enderror"
                               id="discordServerIdInput" name="discord_server_id" placeholder="123456789012345678"
                               value="{{ old('discord_server_id', theme_config('discord_server_id')) }}">
                        <small class="form-text text-muted">
                            Active d'abord le widget sur Discord : Paramètres du serveur → Widget, puis copie l'ID du serveur.
                        </small>
                        @error('discord_server_id')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <h6 class="mb-3">Réseaux sociaux (footer)</h6>

                    <div class="mb-3">
                        <label for="footerDiscordInput" class="form-label">Lien Discord</label>
                        <input type="text" class="form-control @error('footer_discord') is-invalid @enderror"
                               id="footerDiscordInput" name="footer_discord" placeholder="https://discord.gg/..."
                               value="{{ old('footer_discord', theme_config('footer_discord')) }}">
                        @error('footer_discord')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="footerTwitterInput" class="form-label">Lien Twitter/X</label>
                        <input type="text" class="form-control @error('footer_twitter') is-invalid @enderror"
                               id="footerTwitterInput" name="footer_twitter" placeholder="https://x.com/..."
                               value="{{ old('footer_twitter', theme_config('footer_twitter')) }}">
                        @error('footer_twitter')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
