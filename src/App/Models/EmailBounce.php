<?php

namespace Akhan619\LaravelSesEventManager\App\Models;

use Akhan619\LaravelSesEventManager\Database\Factories\EmailBounceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailBounce extends Model
{
    use HasFactory;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('laravel-ses-event-manager.database_name_prefix').'_email_bounces';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return EmailBounceFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'bounce_type',
        'bounce_sub_type',
        'feedback_id',
        'action',
        'status',
        'diagnostic_code',
        'reporting_mta',
        'bounced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'message_id'            => 'string',
        'bounce_type'           => 'string',
        'bounce_sub_type'       => 'string',
        'feedback_id'           => 'string',
        'action'                => 'string',
        'status'                => 'string',
        'diagnostic_code'       => 'string',
        'reporting_mta'         => 'string',
        'bounced_at'            => 'datetime',
    ];

    /**
     * Get the email that the bounce belongs to.
     */
    public function email()
    {
        return $this->belongsTo(Email::class, 'message_id', 'message_id');
    }
}
