<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-ses-event-manager.database_name_prefix') . '_email_delivery_delays', function (Blueprint $table) {
            $table->id();
            $table->string('message_id');
            $table->string('delay_type')->nullable();
            $table->timestamp('expiration_time')->nullable();
            $table->timestamp('delayed_at')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('message_id')->on(config('laravel-ses-event-manager.database_name_prefix') . '_emails')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-ses-event-manager.database_name_prefix') . '_email_delivery_delays');
    }
};
