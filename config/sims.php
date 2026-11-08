<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SIMS Mobile Version & Force Update Config
    |--------------------------------------------------------------------------
    | Digunakan untuk mengontrol pembaruan wajib (force update) aplikasi mobile.
    |
    */
    'min_mobile_version'    => env('MIN_MOBILE_VERSION', '1.5.8'),
    'latest_mobile_version' => env('LATEST_MOBILE_VERSION', '1.5.8'),
    'force_mobile_update'   => (bool) env('FORCE_MOBILE_UPDATE', true),
    'play_store_url'        => env('PLAY_STORE_URL', 'https://play.google.com/store/apps/details?id=com.sman1gianyar.sims_mobile'),
];
