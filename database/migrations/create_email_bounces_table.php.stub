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
        Schema::create(config('laravel-ses-event-manager.database_name_prefix') . '_email_bounces', function (Blueprint $table) {
            $table->id();
            $table->string('message_id');
            $table->string('bounce_type')->nullable();
            $table->string('bounce_sub_type')->nullable();
            $table->string('feedback_id')->nullable();
            $table->string('action')->nullable();
            $table->string('status')->nullable();
            $table->string('diagnostic_code')->nullable();
            $table->string('reporting_mta')->nullable();
            $table->timestamp('bounced_at')->nullable();
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
        Schema::dropIfExists(config('laravel-ses-event-manager.database_name_prefix') . '_email_bounces');
    }
};
