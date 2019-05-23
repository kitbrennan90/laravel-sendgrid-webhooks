<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LaravelSendgridWebhooks\Models\SendgridWebhookEvent;
use LaravelSendgridWebhooks\ServiceProvider;

class ModelTest extends Orchestra\Testbench\TestCase
{
    use DatabaseTransactions;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function testCategoryAccessor()
    {
        $category = "dog facts";

        $newModel = new SendgridWebhookEvent();
        $newModel->timestamp = 1000000;
        $newModel->email = "test1@example.com";
        $newModel->event = \LaravelSendgridWebhooks\Enums\EventEnum::BOUNCE;
        $newModel->sg_event_id = "123";
        $newModel->sg_message_id = "456";
        $newModel->payload = [];
        $newModel->categories = [$category];
        $newModel->save();

        $this->assertIsArray($newModel->categories);
        $this->assertCount(1, $newModel->categories);
        $this->assertEquals($category, $newModel->categories[0]);
        $this->assertIsString($newModel->category);
        $this->assertEquals($category, $newModel->category);
    }

    public function testCategoryMutator()
    {
        $originalCategory = "iguana facts";
        $newCategory = "spider facts";

        $newModel = new SendgridWebhookEvent();
        $newModel->timestamp = 1000000;
        $newModel->email = "test1@example.com";
        $newModel->event = \LaravelSendgridWebhooks\Enums\EventEnum::BOUNCE;
        $newModel->sg_event_id = "123";
        $newModel->sg_message_id = "456";
        $newModel->payload = [];
        $newModel->categories = [$originalCategory];
        $newModel->save();

        $newModel->category = $newCategory;
        $newModel->save();

        $this->assertIsArray($newModel->categories);
        $this->assertCount(1, $newModel->categories);
        $this->assertEquals($newCategory, $newModel->categories[0]);
        $this->assertIsString($newModel->category);
        $this->assertEquals($newCategory, $newModel->category);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
