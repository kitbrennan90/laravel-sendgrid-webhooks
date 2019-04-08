<?php

Route::group(['namespace' => 'LaravelSendgridWebhooks\Http\Controllers'], function () {
    Route::post(
        'sendgrid/webhook',
        [
            'as' => 'sendgrid.webhook',
            'uses' => 'WebhookController@post'
        ]
    );
});
