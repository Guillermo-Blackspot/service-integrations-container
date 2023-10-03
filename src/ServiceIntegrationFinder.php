<?php

namespace BlackSpot\ServiceIntegrationsContainer;

use BlackSpot\ServiceIntegrationsContainer\ServiceIntegration;
use BlackSpot\ServiceIntegrationsContainer\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;

trait ServiceIntegrationFinder
{
    protected $serviceIntegrationsFound = [];

    /**
     * Build the service integration query to find
     * 
     * @param int|null $serviceIntegrationId
     * @param array $resolverMethods[columnResolver, idResolver]
     * 
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getServiceIntegrationQuery($serviceIntegrationId = null, $provider = null, $callback = null)
    {
        $query = $this->getServiceIntegrationBaseQuery();

        if (! is_null($query)) throw new Exception('Service integration query can not be builded.');
        
        $this->addConditions($serviceIntegrationId, $provider, $callback);

        return $query;
    }

    private function resolveServiceIntegrationId($serviceId, $provider = null)
    {
        if (! is_null($serviceId)) {
            // Specific id
            return (int) $serviceId;
        }else if (self::class == ServiceProvider::getFromConfig('model', ServiceIntegration::class) && isset($this->id)) {
            // Determine if is a instance of ServiceIntegration
            return (int) $this->id;
        }else if (
                ! is_null($provider) && 
                method_exists($this, 'getServiceIntegrationResolvers') && 
                Arr::has($this->getServiceIntegrationResolvers(), "{$provider}.id_resolver")
            ) {            
                // Try to resolve from the getServiceIntegrationResolvers method
            return (int) $this->getServiceIntegrationResolvers()[$provider]['id_resolver'];
        }
    }

    public function resolveServiceIntegrationFromInstance($instance, $serviceIntegrationId = null)
    {
        if (get_class($instance) == ServiceProvider::getFromConfig('model', ServiceIntegration::class)) {            
            // If the ServiceIntegration Model is the same
            if ($this->id == $serviceIntegrationId) {                
                return $instance;
            }
        }else if (method_exists($instance, 'service_integration') && $instance->relationLoaded('service_integration')) {
            // If the relationships was loaded

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

    public function getLoadedServiceIntegration($serviceIntegrationId)
    {            
        return $this->serviceIntegrationsFound[$serviceIntegrationId];
    }

    public function putServiceIntegrationFound($serviceIntegration)
    {
        return $this->serviceIntegrationsFound[$serviceIntegration->id] = $serviceIntegration;
    }

    public function addConditions($serviceIntegrationId, $provider = null, $callback = null)
    {
        if (($serviceIntegrationId = $this->resolveServiceIntegrationId($serviceIntegrationId, $provider)) != null) {
            $query->where('id', $serviceIntegrationId);
        }else {
            $conditions = $this->addWhereCustomColumCondition($query, $provider);

            if (empty($conditions)) $query->where('id', 'model-not-exists--');
        }

        return $callback($query);
    }

    /**
     * Set where id condition
     *
     * @param \Illuminate\Database\Eloquent\Query  $query
     * @param string  $provider
     * @return $this
     */
    private function addWhereCustomColumCondition(&$query, $provider = null)
    {
        $conditions = [];

        if (method_exists($this, 'getServiceIntegrationResolvers') && ! is_null($provider)) {
            if (Arr::has(($conditions = $this->getServiceIntegrationResolvers()), "{$provider}.column_resolver")) {            
                foreach ($conditions[$provider]['column_resolver'] as $condition) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            }
        }

        return $conditions;
    }

    /**
     * Check if service_integration relationship exists
     *
     * @return \Illuminate\Database\Eloquent\Query\Builder
     * 
     * @throws \LogicException
     */
    private function getServiceIntegrationBaseQuery()
    {                
        if (method_exists($this, 'service_integration')) {
            return $this->service_integration()->getQuery();
        }

        $modelClass = ServiceProvider::getFromConfig('model', ServiceIntegration::class);
        $modelClass = '\\'.$modelClass;
        return (new $modelClass)->query();
    }
}