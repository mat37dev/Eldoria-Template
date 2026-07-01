<?php

return [
    'color_accent' => ['required', 'string', 'max:7'],
    'color_accent_secondary' => ['required', 'string', 'max:7'],
    'hero_slogan' => ['nullable', 'string', 'max:500'],
    'hero_image' => ['nullable', 'string', 'max:255'],
    'show_section_shop' => ['required', 'in:0,1'],
    'show_section_vote' => ['required', 'in:0,1'],
    'show_section_forum' => ['required', 'in:0,1'],
    'footer_discord' => ['nullable', 'string', 'max:255'],
    'footer_twitter' => ['nullable', 'string', 'max:255'],
];
