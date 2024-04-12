<?php

declare(strict_types=1);

use PrestaShop\Module\DistributionApiMwg\DistributionApi;

if (!defined('_PS_VERSION_')) {
    exit;
}
class ps_distributionapimwg extends \Module
{
    public function __construct()
    {
        $this->name = 'ps_distributionapimwg';
        $this->displayName = $this->trans('Distribution API MWG', [], 'Modules.Distributionapimwg.Admin');
        $this->description = $this->trans('Download and upgrade MWG\'s native modules.', [], 'Modules.Distributionapimwg.Admin');
        $this->author = 'Minh Huy';
        $this->version = '1.1.0';
        $this->ps_versions_compliancy = ['min' => '8.0.2', 'max' => _PS_VERSION_];
        $this->tab = 'market_place';
        parent::__construct();
    }
    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('actionListModules')
            && $this->registerHook('actionBeforeInstallModule')
            && $this->registerHook('actionBeforeUpgradeModule')
            ;
    }

    /**
     * @return array<array<string, string>>
     */
    public function hookActionListModules(): array
    {
        return $this->getDistributionApi()->getModuleList();
    }

    /**
     * @param string[] $params
     *
     * @return void
     */
    public function hookActionBeforeInstallModule(array $params): void
    {
        $distributionApi = $this->getDistributionApi();
        if (!isset($params['moduleName']) || $distributionApi->isModuleOnDisk($params['moduleName'])) {
            return;
        }

        $distributionApi->downloadModule($params['moduleName']);
    }

    /**
     * @param string[] $params
     *
     * @return void
     */
    public function hookActionBeforeUpgradeModule(array $params): void
    {
        if (!isset($params['moduleName']) || !empty($params['source'])) {
            return;
        }

        $this->getDistributionApi()->downloadModule($params['moduleName']);
    }

    private function getDistributionApi(): DistributionApi
    {
        /** @var DistributionApi $distributionApi */
        $distributionApi = $this->get('distributionapimwg.distribution_api');

        return $distributionApi;
    }

}