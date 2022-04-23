<?php

namespace Akhan619\LaravelSesEventManager\App\Models;

use Akhan619\LaravelSesEventManager\Database\Factories\EmailSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSubscription extends Model
{
    use HasFactory;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('laravel-ses-event-manager.database_name_prefix') . '_email_subscriptions';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return EmailSubscriptionFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'contact_list',
        'new_topic_preferences',
        'old_topic_preferences',
        'notified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'message_id'                    =>  'string',
        'contact_list'                  =>  'string',
        'new_topic_preferences'         =>  'array',
        'old_topic_preferences'         =>  'array',
        'notified_at'                   =>  'datetime',
    ];

    /**
     * Get the email that the subscription belongs to.
     */
    public function email()
    {
        return $this->belongsTo(Email::class, 'message_id', 'message_id');
    }
}
