<?php

return [
    'color_accent' => ['required', 'string', 'max:7'],
    'color_accent_secondary' => ['required', 'string', 'max:7'],
    'hero_slogan' => ['nullable', 'string', 'max:500'],
    'hero_image' => ['nullable', 'string', 'max:255'],
    'show_section_shop' => ['required', 'in:0,1'],
    'show_section_vote' => ['required', 'in:0,1'],
    'trailer_url' => ['nullable', 'url', 'max:255'],
    'discord_server_id' => ['nullable', 'string', 'max:32', 'regex:/^\d*$/'],
    'footer_discord' => ['nullable', 'string', 'max:255'],
    'footer_twitter' => ['nullable', 'string', 'max:255'],
];
