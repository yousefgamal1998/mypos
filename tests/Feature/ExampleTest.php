<?php

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

it('redirects the root path to the localized entry point', function () {
    $response = $this->get('/');

    $response->assertRedirect(
        LaravelLocalization::getLocalizedURL(app()->getLocale(), url('/'))
    );
});
