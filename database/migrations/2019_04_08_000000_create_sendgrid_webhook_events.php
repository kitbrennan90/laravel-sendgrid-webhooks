<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendgridWebhookEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendgrid_webhook_events', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->timestamp('timestamp');
            $table->string('email')->index();
            $table->string('event')->index();
            $table->string('category')->nullable()->index();
            $table->string('sg_event_id')->unique();
            $table->string('sg_message_id')->index();
            $table->jsonb('payload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sendgrid_webhook_events');
    }
}
