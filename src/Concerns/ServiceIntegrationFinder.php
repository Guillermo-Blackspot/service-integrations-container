<?php

namespace BlackSpot\ServiceIntegrationsContainer\Concerns;

use BlackSpot\ServiceIntegrationsContainer\ServiceProvider;

trait ServiceIntegrationFinder
{   
    /**
     * Build the service integration query to find
     * 
     * @param int|null $serviceIntegrationId
     * 
     * @return \Illuminate\Database\Query\Builder|null
     */
    protected function getServiceIntegrationQuery($serviceIntegrationId = null)
    {
        $serviceIntegrationModel     = config(ServiceProvider::PACKAGE_NAME.'.model');
        $serviceIntegrationTableName = $serviceIntegrationModel::TABLE_NAME;
        $query                       = DB::table($serviceIntegrationTableName)

        if (!is_null($serviceIntegrationId)) {
            $query = $query->where('id', $serviceIntegrationId);
        }elseif (isset($this->id) && self::class == config(ServiceProvider::PACKAGE_NAME.'.model')) {
            $query = $query->where('id', $this->id);
        }else if (isset($this->service_integration_id)){
            $query = $query->where('id', $this->service_integration_id);
        }else if (method_exists($this, 'getMainServiceIntegrationId')){
            $query = $query->where('id', $this->getMainServiceIntegrationId());
        }else if (method_exists($this, 'getMainServiceIntegrationOwnerId') && method_exists($this,'getMainServiceIntegrationOwnerType')){
            $query = $query->where('owner_type', $this->getMainServiceIntegrationOwnerType())->where('owner_id', $this->getMainServiceIntegrationOwnerId());
        }else{
            $query = $query->where('owner_type', 'not-exists-expecting-null');
        }

        return $query;
    }
}