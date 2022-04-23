<?php

namespace Akhan619\LaravelSesEventManager\App\Models;

use Akhan619\LaravelSesEventManager\Database\Factories\EmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
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

        $this->table = config('laravel-ses-event-manager.database_name_prefix') . '_emails';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return EmailFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'email',
        'name',
        'has_send',
        'has_rendering_failure',
        'has_reject',
        'has_delivery',
        'has_bounce',
        'has_complaint',
        'has_delivery_delay',
        'has_subscription',
        'has_open',
        'has_click'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'message_id'                =>  'string',
        'email'                     =>  'string',
        'name'                      =>  'string',
        'has_send'                  =>  'boolean',
        'has_rendering_failure'     =>  'boolean',
        'has_reject'                =>  'boolean',
        'has_delivery'              =>  'boolean',
        'has_bounce'                =>  'boolean',
        'has_complaint'             =>  'boolean',
        'has_delivery_delay'        =>  'boolean',
        'has_subscription'          =>  'boolean',
        'has_open'                  =>  'boolean',
        'has_click'                 =>  'boolean',
    ];

    /**
     * Get the email clicks for the email.
     */
    public function clicks()
    {
        return $this->hasMany(EmailClick::class, 'message_id', 'message_id');
    }

    /**
     * Get the email opens for the email.
     */
    public function opens()
    {
        return $this->hasMany(EmailOpen::class, 'message_id', 'message_id');
    }

    /**
     * Get the bounce event details for the email.
     */
    public function bounce()
    {
        return $this->hasOne(EmailBounce::class, 'message_id', 'message_id');
    }

    /**
     * Get the complaint event details for the email.
     */
    public function complaint()
    {
        return $this->hasOne(EmailComplaint::class, 'message_id', 'message_id');
    }

    /**
     * Get the delivery event details for the email.
     */
    public function delivery()
    {
        return $this->hasOne(EmailDelivery::class, 'message_id', 'message_id');
    }

    /**
     * Get the send event details for the email.
     */
    public function send()
    {
        return $this->hasOne(EmailSend::class, 'message_id', 'message_id');
    }

    /**
     * Get the reject event details for the email.
     */
    public function reject()
    {
        return $this->hasOne(EmailReject::class, 'message_id', 'message_id');
    }

    /**
     * Get the rendering failure event details for the email.
     */
    public function renderingFailure()
    {
        return $this->hasOne(EmailRenderingFailure::class, 'message_id', 'message_id');
    }

    /**
     * Get the delivery delay event details for the email.
     */
    public function deliveryDelay()
    {
        return $this->hasOne(EmailDeliveryDelay::class, 'message_id', 'message_id');
    }

    /**
     * Get the subscription event details for the email.
     */
    public function subscription()
    {
        return $this->hasOne(EmailSubscription::class, 'message_id', 'message_id');
    }
}
