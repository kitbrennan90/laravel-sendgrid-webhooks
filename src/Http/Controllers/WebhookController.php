<?php

namespace LaravelSendgridWebhooks\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaravelSendgridWebhooks\Enums\EventEnum;
use LaravelSendgridWebhooks\Models\SendgridWebhookEvent;
use Psr\Log\LogLevel;

/**
 * Class WebhookController
 * Ingresses any Sendgrid webhooks
 *
 * @package LaravelSendgridWebhooks\Http\Controllers
 */
class WebhookController extends Controller
{
    use ValidatesRequests;

    /**
     * @var SendgridWebhookEvent
     */
    private $sendgridWebhookEvent;

    /**
     * WebhookController constructor.
     *
     * @param SendgridWebhookEvent $sendgridWebhookEvent
     */
    public function __construct(SendgridWebhookEvent $sendgridWebhookEvent)
    {
        $this->sendgridWebhookEvent = $sendgridWebhookEvent;
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function post(Request $request)
    {
        $payload = $request->input();
        $validator = Validator::make(
            $payload,
            [
                '*.email' => 'required|email',
                '*.timestamp' => 'required|integer',
                '*.event' => 'required|in:' . implode(',', EventEnum::getAll()),
                '*.sg_event_id' => 'required|string',
                '*.sg_message_id' => 'required|string',
                '*.category' => 'required|string',
            ]
        );
        if ($validator->fails()) {
            $this->logMalformedPayload($payload, $validator->errors()->all());
            throw new ValidationException($validator);
        }

        foreach ($payload as $event) {
            $this->processEvent($event);
        }
    }

    /**
     * Processes an individual event
     *
     * @param $event
     */
    private function processEvent(array $event): void
    {
        if ($this->sendgridWebhookEvent->where('sg_event_id', $event['sg_event_id'])->count()) {
            $this->logDuplicateEvent($event);
            return;
        }

        $newEvent = new SendgridWebhookEvent;
        $newEvent->timestamp = $event['timestamp'];
        $newEvent->email = $event['email'];
        $newEvent->event = $event['event'];
        $newEvent->sg_event_id = $event['sg_event_id'];
        $newEvent->sg_message_id = $event['sg_message_id'];
        $newEvent->category = $event['category'];
        $newEvent->payload = $event;
        $newEvent->save();
    }

    /**
     * Logs a message that we have received a malformed webhook
     * If the webhook was sent by Sendgrid then this may indicate Sendgrid has changed their payload structure and
     * therefore this library will need to be updated.
     *
     * Note: there is no way of validating that this webhook was actually sent by Sendgrid, so the malformation could
     * be the result of a malicious third party.
     *
     * @param array $event
     */
    private function logMalformedPayload($payload, array $validationErrors)
    {
        if (config('sendgridwebhooks.log_malformed_payload')) {
            Log::log(
                config('sendgridwebhooks.log_malformed_payload_level'),
                'Malformed Sendgrid webhook received',
                [
                    'payload' => $payload,
                    'validation_errors' => $validationErrors,
                ]
            );
        }
    }

    /**
     * Logs a message that we have received a duplicate webhook for an event
     *
     * @param array $event
     */
    private function logDuplicateEvent(array $event)
    {
        if (config('sendgridwebhooks.log_duplicate_events')) {
            Log::log(
                config('sendgridwebhooks.log_duplicate_events_level'),
                'Duplicate Sendgrid Webhook received',
                $event
            );
        }
    }
}
