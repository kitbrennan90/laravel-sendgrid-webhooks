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


## Using the library

### Querying records

This library uses a standard Laravel Eloquent model, so you can therefore query it as you would any other model.

```php
// Include the class
use \LaravelSendgridWebhooks\Models\SendgridWebhookEvent;

////////////////////

// Get all records:
SendgridWebhookEvent::all();

// Get all by message ID:
$sendgridMessageId = 'abc123';
SendgridWebhookEvent::where('sg_message_id', $sendgridMessageId)->all();

// Get all by event ID:
$sendgridEventId = 'xyz987';
SendgridWebhookEvent::where('sg_event_id', $sendgridEventId)->all();

// Get all by event type
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::PROCESSED)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::DEFERRED)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::DELIVERED)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::OPEN)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::CLICK)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::BOUNCE)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::DROPPED)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::SPAMREPORT)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::UNSUBSCRIBE)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::GROUP_UNSUBSCRIBE)->all();
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::GROUP_RESUBSCRIBE)->all();

// Count all bounces
SendgridWebhookEvent::where('event', LaravelSendgridWebhooks\Enums::BOUNCE)->count();
```

### Interacting with a record

Accessing data included with all event types:

```php
// Get a record
$event = SendgridWebhookEvent::first();

// Included with all event types
$event->timestamp;
$event->email;
$event->event;
$event->category;
$event->sg_event_id;
$event->sg_message_id;
$event->payload; // Array of full payload sent by Sendgrid
```

Some data is only included with specific events. You can find out what these attributes are here: https://sendgrid.com/docs/API_Reference/Event_Webhook/event.html#-Event-objects

We include this data under the payload array within a record. For example:
```php
// Get a record
$event = SendgridWebhookEvent::first();

// Access the reason attribute, included on 'dropped' and 'bounced' events.
$event->payload['reason'];
```
