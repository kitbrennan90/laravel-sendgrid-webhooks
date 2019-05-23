<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use LaravelSendgridWebhooks\Models\SendgridWebhookEvent;

class UpdateCategoryColumnToArray extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sendgrid_webhook_events', function (Blueprint $table) {
            $table->jsonb('categories')->default(json_encode([]))->index();
        });

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Move old `category` values over to new `categories` json array
        switch ($driver) {
            case 'sqlite': {
                DB::table('sendgrid_webhook_events')
                    ->whereNotNull('category')
                    ->update(['categories' => DB::raw("CAST('[\"' || category || '\"]' AS json)")]);
                break;
            }

            default: {
                DB::table('sendgrid_webhook_events')
                    ->whereNotNull('category')
                    ->update(['categories' => DB::raw("CAST(concat('[\"', category, '\"]') AS json)")]);
                break;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sendgrid_webhook_events', function (Blueprint $table) {
            $table->dropColumn(['categories']);
        });
    }
}
