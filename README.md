# Laravel Sendgrid Webhooks

This package enables your Laravel application to receive event webhooks from Sendgrid, and will automatically store those 
webhooks in your database. The package also fires Laravel events so you can hook in to the webhooks and take your own 
actions.


## Installation

### Getting the package

The best way to install this package is with composer. Run this from your command line: 
```php
composer require kitbrennan90/laravel-sendgrid-webhooks ~1.0
```

### Run the migrations

This package will create a table called `sendgrid_webhook_events` which will be used to store all the Sendgrid webhooks received.

Once you have included the package, this migration will run automatically with your normal migrations, so just call:

```php
php artisan migrate
```

### Copy the config file (optional)

This library works without any local configuration, however you may want to use the config file in order to tweak the logs you receive (eg. to receive logs when you receive duplicate events). 

Call the command below to copy the package config files:
```php
php artisan vendor:publish --provider="LaravelSendgridWebhooks\ServiceProvider"
```

### Tell Sendgrid to use your new event webhook URL

Head over to https://app.sendgrid.com/settings/mail_settings and click on the 'Event Notification' section.

Your HTTP Post URL is: `https://yourwebsite.com/sendgrid/webhook`

