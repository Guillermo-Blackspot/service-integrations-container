<?php

namespace BlackSpot\ServiceIntegrationsContainer\Concerns;

use BlackSpot\ServiceIntegrationsContainer\Models\ServiceIntegration;
use BlackSpot\ServiceIntegrationsContainer\ServiceIntegrationsContainerProvider;

trait ServiceIntegrationFinder
{   
    /**
     * Build the service integration query to find
     * 
     * @param int|null $serviceIntegrationId
     * 
     * @return \Illuminate\Database\Query\Builder|null
     */
    protected function getServiceIntegrationQueryFinder($serviceIntegrationId = null, $resolverMethods = [])
    {
        $serviceIntegrationModel     = ServiceIntegrationsContainerProvider::getFromConfig('model', ServiceIntegration::class);
        $serviceIntegrationTableName = $serviceIntegrationModel::TABLE_NAME;
        $query                       = DB::table($serviceIntegrationTableName)

        if (!is_null($serviceIntegrationId)) {
            $query = $query->where('id', $serviceIntegrationId);
        }elseif (isset($this->id) && self::class == $serviceIntegrationModel) {
            $query = $query->where('id', $this->id);
        }else if (isset($this->service_integration_id)){
            $query = $query->where('id', $this->service_integration_id);
        }else if (method_exists($this, 'getMainServiceIntegrationId')){
            $query = $query->where('id', $this->getMainServiceIntegrationId());
        }else if (method_exists($this, 'getMainServiceIntegrationOwnerId') && method_exists($this,'getMainServiceIntegrationOwnerType')){
            $query = $query->where('owner_type', $this->getMainServiceIntegrationOwnerType())->where('owner_id', $this->getMainServiceIntegrationOwnerId());
        }else if(is_array($resolverMethods) && $resolverMethods != []){
            if (method_exists($this, $resolverMethods[0]) && method_exists($this, $resolverMethods[1])) {
                $query = $query
                            ->where('owner_id', $this->{$resolverMethods[0]}())
                            ->where('owner_type', $this->{$resolverMethods[1]}());
            }else{
                $query = $query->where('owner_type', 'not-exists-expecting-null');
            }
        }else if(is_string($resolverMethods) && $resolverMethods){
            if (method_exists($this, $resolverMethods)) {
                $query = $query->where('id', $this->{$resolverMethods}())
            }else{
                $query = $query->where('id', 'not-exists-expecting-null');
            }
        }else{
            $query = $query->where('id', 'not-exists-expecting-null');
        }

        return $query;
    }
}