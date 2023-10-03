<?php

namespace BlackSpot\ServiceIntegrationsContainer;

use BlackSpot\ServiceIntegrationsContainer\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceIntegration extends Model
{
    /** 
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_integrations';
    public const TABLE_NAME = 'service_integrations';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
    ];

    public const STRIPE_SERVICE = 'Stripe'; 
    public const STRIPE_SERVICE_SHORT_NAME = 'str'; 
    public const SYSTEM_CHARGES_SERVICE = 'System_Charges'; 
    public const SYSTEM_CHARGES_SERVICE_SHORT_NAME = 'sys_ch';     

    /**
     * Overwrite cast json method
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Shortened payload 
     * 
     * usefully when needs to display the payload on readonly
     *
     * @return void
     */
    public function getShortenedPayloadAttribute()
    {
        $shortened = [];

        $payloadAttribute = ServiceProvider::getFromConfig('payload_column', 'payload');

        foreach ($this->{$payloadAttribute} as $property => $value) {
            $shortened[$property] = Str::limit($value, 15);
        }

        return $shortened;
    }

    public function active()
    {
        return $this->active;
    }

    public function disabled()
    {
        return ! $this->active;
    }

    /**
     * Determine that the integrated service must be active
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return void
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Determine that the integrated service must be disabled
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return void
     */
    public function scopeDisabled($query)
    {
        return $query->where('active', false);
    }    

    /**
     * Get the owner of the service integration
     * 
     * @return object
     */
    public function owner()
    {
        return $this->morphTo('owner');
    }
}
