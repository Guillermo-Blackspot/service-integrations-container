<?php

namespace BlackSpot\ServiceIntegrationsContainer\Concerns;

use BlackSpot\ServiceIntegrationsContainer\Models\ServiceIntegration;
use BlackSpot\ServiceIntegrationsContainer\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

trait ServiceIntegrationFinder
{   
    protected $serviceIntegrationsFound = [];
    protected $resolverMethodFound = false;

    /**
     * Build the service integration query to find
     * 
     * @param int|null $serviceIntegrationId
     * @param array $resolverMethods[columnResolver, idResolver]
     * 
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getServiceIntegrationQueryFinder($serviceIntegrationId = null, $provider = null)
    {
        $serviceIntegrationClass = ServiceProvider::getFromConfig('model', ServiceIntegration::class);

        /* 
         * Evaluates if the "service_integration" relationship exists,
         * 
         * if exists return the query
         */

        $belongsTo = $this->queryFromBelongsToRelationship();

        if (! is_null($belongsTo)) {
            return $belongsTo;
        }

        $modelToInstance = '\\'.$serviceIntegrationClass;

        /*
         * Try to resolve the service integration to find
         * 
         * by id or by the resolver methods
         */
        $query = (new $modelToInstance)->query();

        $this->whereIdCondition($query, $serviceIntegrationId, $provider)
            ->whereCustomColumCondition($query, $provider);


        if (! $this->resolverMethodFound) {
            $query->where('id', 'defined-resolver-not-found');
        }

        return $query;
    }


    public function resolveServiceIntegrationFromInstance($instance, $serviceIntegrationId)
    {
        // Is the ServiceIntegration Model
        if (isset($instance) && get_class($instance) == ServiceProvider::getFromConfig('model', ServiceIntegration::class)) {
            if (! is_null($serviceIntegrationId) && $this->id == $serviceIntegrationId) {                
                return $instance;
            }
        }
        // If the relationships is loaded
        else if (method_exists($instance, 'service_integration') && $instance->relationLoaded('service_integration')) {

            if ($instance->service_integration->id != $serviceIntegrationId) {
                throw new LogicException('The relationship "service_integration" cannot be different at the foreign_key.');    
            }

            return $instance->service_integration;
        }
    }

    /**
     * Determine if the given service integration id was already found
     *
     * @param int $serviceIntegrationId
     * @return bool
     */
    public function serviceIntegrationWasLoaded($serviceIntegrationId)
    {
        if (! $serviceIntegrationId) {
            return false;
        }

        return isset($this->serviceIntegrationsFound[$serviceIntegrationId]);
    }

    /**
     * Get the given service integration found
     *
     * @param int $serviceIntegrationId
     * @return \BlackSpot\ServiceIntegrationsContainer\Models\ServiceIntegration
     */
    public function getServiceIntegrationLoaded($serviceIntegrationId)
    {            
        return $this->serviceIntegrationsFound[$serviceIntegrationId];
    }

    public function putServiceIntegrationFound($serviceIntegration)
    {
        return $this->serviceIntegrationsFound[$serviceIntegration->id] = $serviceIntegration;
    }


    /**
     * Set where id condition
     *
     * @param \Illuminate\Database\Eloquent\Query  $query
     * @param int  $serviceIntegrationId
     * @param string  $provider
     * @return $this
     */
    private function whereIdCondition(&$query, $serviceIntegrationId, $provider = null)
    {
        if ($this->resolverMethodFound) {
            return $this;
        }
        
        // Specific id
        if (! is_null($serviceIntegrationId)) {
            $serviceIntegrationId = (int) $serviceIntegrationId;
        }

        // Determine if is a instance of ServiceIntegration
        else if (isset($this->id) && self::class == ServiceProvider::getFromConfig('model', ServiceIntegration::class)) {
            $serviceIntegrationId = (int) $this->id;
        }

        // Try to resolve from the getServiceIntegrationResolvers method
        else if (method_exists($this, 'getServiceIntegrationResolvers') && ! is_null($provider) && Arr::has(($resolverMethods = $this->getServiceIntegrationResolvers()), "{$provider}.idResolver")) {            
            $serviceIntegrationId = (int) $resolverMethods[$provider]['idResolver'];
        }
        
        // Ignoring next resolvers
        if ($serviceIntegrationId) {
            $this->resolverMethodFound = true;
            $query->where('id', $serviceIntegrationId);
        }

        return $this;
    }

    /**
     * Set where id condition
     *
     * @param \Illuminate\Database\Eloquent\Query  $query
     * @param string  $provider
     * @return $this
     */
    private function whereCustomColumCondition(&$query, $provider = null)
    {
        if ($this->resolverMethodFound) {
            return $this;
        }

        $conditions = [];

        // Try to resolve from the getServiceIntegrationResolvers method
        if (method_exists($this, 'getServiceIntegrationResolvers') && ! is_null($provider)) {
            if (Arr::has(($conditions = $this->getServiceIntegrationResolvers()), "{$provider}.columnResolver")) {            
                foreach ($conditions[$provider]['columnResolver'] as $condition) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
        }

        if (! empty($conditions)) {
            $this->resolverMethodFound = true;
        }

        return $this;
    }

    /**
     * Check if service_integration relationship exists
     *
     * @return \Illuminate\Database\Eloquent\Query\Builder
     * 
     * @throws \LogicException
     */
    private function queryFromBelongsToRelationship()
    {                
        if (method_exists($this, 'service_integration')) {
            return $this->service_integration()->getQuery();
        }
    
        // Relationship not defined but the service_integration_id is present
        if (isset($this->service_integration_id)) {
            throw new LogicException("The belongsTo \"service_integration\" relationship not defined.", 1);                
        }
    }
}