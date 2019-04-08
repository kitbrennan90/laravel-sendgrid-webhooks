<?php

namespace LaravelSendgridWebhooks\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SendgridWebhookEvent
 *
 * @package LaravelSendgridWebhooks\Models
 *
 * @property string $category
 * @property Carbon $created_at
 * @property string $email
 * @property string $event
 * @property int $id
 * @property string $sg_event_id
 * @property string $sg_message_id
 * @property array $payload
 * @property Carbon $timestamp
 * @property Carbon $updated_at
 */
class SendgridWebhookEvent extends Model
{
    protected $dates = ['timestamp'];

    protected $casts = [
        'payload' => 'array',
    ];
}
