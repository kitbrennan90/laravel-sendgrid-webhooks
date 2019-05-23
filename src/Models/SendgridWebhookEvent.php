<?php

namespace LaravelSendgridWebhooks\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SendgridWebhookEvent
 *
 * @package LaravelSendgridWebhooks\Models
 *
 * @property string|null $category Backwards compatible, returns first category from $categories. DO NOT USE
 * @property array|string[] $categories
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
        'categories' => 'array',
    ];

    /*********************************************/
    /************* ACCESSORS **/
    /*********************************************/

    /**
     * Backwards compatible accessor. Please use $categories instead and access the array of categories
     * @return null|string
     */
    public function getCategoryAttribute()
    {
        if (count($this->categories)) {
            return $this->categories[0];
        }

        return null;
    }

    /*********************************************/
    /************* MUTATORS **/
    /*********************************************/

    /**
     * Backwards compatible mutator. Please use $categories instead and mutate the array of categories
     *
     * @param string $value
     */
    public function setCategoryAttribute(string $value)
    {
        $this->attributes['categories'] = json_encode([$value]);
    }
}
