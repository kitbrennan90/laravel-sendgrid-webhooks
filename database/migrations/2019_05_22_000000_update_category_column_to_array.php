<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCategoryColumnToArray extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        // Move old `category` values over to new `categories` json array
        switch ($driver) {
            case 'sqlite': {
                Schema::table('sendgrid_webhook_events', function (Blueprint $table) {
                    $table->jsonb('categories')->nullable()->index();
                });

                DB::table('sendgrid_webhook_events')
                    ->whereNotNull('category')
                    ->update(['categories' => DB::raw("CAST('[\"' || category || '\"]' AS json)")]);
                break;
            }

            case 'mysql': {
                Schema::table('sendgrid_webhook_events', function (Blueprint $table) {
                    $table->jsonb('categories')->default(json_encode([]));
                    $table->index([DB::raw('categories(767)')], 'categories_index');
                });

                DB::table('sendgrid_webhook_events')
                    ->whereNotNull('category')
                    ->update(['categories' => DB::raw("concat('{\"', category, '\"}')")]);
                break;
            }

            default: {
                Schema::table('sendgrid_webhook_events', function (Blueprint $table) {
                    $table->jsonb('categories')->default(json_encode([]))->index();
                });

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
