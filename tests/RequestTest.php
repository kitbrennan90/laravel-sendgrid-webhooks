<?php

use Carbon\Carbon;
use LaravelSendgridWebhooks\Models\SendgridWebhookEvent;

class RequestTest extends Orchestra\Testbench\TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function testSuccessfulWebhook()
    {
        // Laravels testing suite does not support posting a json string, so we have to load our JSON payload, decode it
        // and then fire it off...
        $payload = json_decode(file_get_contents(__DIR__ . '/stubs/request_payload.json'));

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(200);

        $processedEvent = SendgridWebhookEvent::where('sg_event_id', 'WiiomrqWErazAXdj782fZw==')->first();
        $this->assertNotNull($processedEvent);
    }

    /**
     * A duplicate webhook should not create the same record twice
     */
    public function testDuplicateWebhook()
    {
        $payload = [
            [
                "email" => "example@test.com",
                "timestamp" => 1554728844,
                "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
                "event" => "processed",
                "category" => "cat facts",
                "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
                "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
            ],
            [
                "email" => "example@test.com",
                "timestamp" => 1554728844,
                "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
                "event" => "processed",
                "category" => "cat facts",
                "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
                "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
            ]
        ];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(200);

        $postWebhookCount = SendgridWebhookEvent::count();
        $this->assertEquals(
            $preWebhookCount + 1,
            $postWebhookCount,
            'Only one new record should be created'
        );
    }

    /**
     * If the payload is not an array it should be rejected
     */
    public function testNonArrayRejected()
    {
        $payload = ['wrong'];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(422);
        $result->assertSee('The 0.email field is required.');

        $postWebhookCount = SendgridWebhookEvent::count();

        $this->assertEquals($preWebhookCount, $postWebhookCount, "No new database rows should be created");
    }

    /**
     * If the payload does not contain an email field it should be rejected
     */
    public function testPayloadWithoutEmailShouldBeRejected()
    {
        $payload = [[
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "category" => "cat facts",
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
        ]];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(422);
        $result->assertSee('The 0.email field is required.');

        $postWebhookCount = SendgridWebhookEvent::count();

        $this->assertEquals($preWebhookCount, $postWebhookCount, "No new database rows should be created");
    }

    /**
     * If the payload does not contain an email field it should be rejected
     */
    public function testPayloadWithoutCategoryShouldBeSuccessful()
    {
        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
        ]];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(200);

        $postWebhookCount = SendgridWebhookEvent::count();

        $this->assertEquals($preWebhookCount + 1, $postWebhookCount);
    }

    /**
     * If the payload does not contain an email field it should be rejected
     */
    public function testPayloadWithArrayCategoryShouldBeSuccessful()
    {
        $messageId = "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.2";
        $category = "bird facts";
        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "category" => [$category],
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => $messageId,
        ]];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(200);

        $postWebhookCount = SendgridWebhookEvent::count();

        $this->assertEquals($preWebhookCount + 1, $postWebhookCount);

        $newEvent = SendgridWebhookEvent::where('sg_message_id', $messageId)->first();
        $this->assertCount(1, $newEvent->categories, "Should be one item in categories array");
        $this->assertEquals($category, $newEvent->categories[0], 'Category should be saved in $categories');
    }

    /**
     * If the payload does not contain an email field it should be rejected
     */
    public function testPayloadWithStringCategoryShouldBeSuccessful()
    {
        $messageId = "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.1";
        $category = "wolf facts";
        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "processed",
            "category" => $category,
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => $messageId,
        ]];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(200);

        $postWebhookCount = SendgridWebhookEvent::count();

        $this->assertEquals($preWebhookCount + 1, $postWebhookCount);

        $newEvent = SendgridWebhookEvent::where('sg_message_id', $messageId)->first();
        $this->assertCount(1, $newEvent->categories, "Should be one item in categories array");
        $this->assertEquals($category, $newEvent->categories[0], 'Category should be saved in $categories');
    }

    /**
     * If the payload does not contain an email field it should be rejected
     */
    public function testPayloadWithUnknownEventShouldBeRejected()
    {
        $payload = [[
            "email" => "example@test.com",
            "timestamp" => 1554728844,
            "smtp-id" => "<14c5d75ce93.dfd.64b469@ismtpd-555>",
            "event" => "wrongwrong",
            "category" => "cat facts",
            "sg_event_id" => "WiiomrqWErazAXdj782fZw==",
            "sg_message_id" => "14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0"
        ]];

        $preWebhookCount = SendgridWebhookEvent::count();

        /** @var \Illuminate\Foundation\Testing\TestResponse $result */
        $result = $this->postJson(
            'sendgrid/webhook',
            $payload
        );
        $result->assertStatus(422);
        $result->assertSee('The selected 0.event is invalid.');

        $postWebhookCount = SendgridWebhookEvent::count();

        $this->assertEquals($preWebhookCount, $postWebhookCount, "No new database rows should be created");
    }

    protected function getPackageProviders($app)
    {
        return [\LaravelSendgridWebhooks\ServiceProvider::class];
    }
}
