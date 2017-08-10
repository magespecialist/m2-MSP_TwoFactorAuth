<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Setup;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use MSP\SecuritySuiteCommon\Model\ConfigMigration;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\DuoSecurity;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var ConfigMigration
     */
    private $configMigration;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(
        EncoderInterface $encoder,
        ConfigMigration $configMigration,
        ConfigInterface $config
    ) {
        $this->encoder = $encoder;
        $this->configMigration = $configMigration;
        $this->config = $config;
    }

    protected function upgradeTo010200(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $adminUserTable = $setup->getTable('admin_user');

        $connection->update($adminUserTable, [
            'msp_tfa_provider' => 'none',
        ], 'msp_tfa_provider=0');

        $connection->update($adminUserTable, [
            'msp_tfa_provider' => 'google'
        ], 'msp_tfa_provider=1');

        $users = $connection->fetchAll($connection->select()->from($adminUserTable));
        foreach ($users as $user) {
            $tfaSecret = $user['msp_tfa_config'];
            if ($tfaSecret) {
                $connection->update($adminUserTable, ['msp_tfa_config' => $this->encoder->encode([
                    'google' => [
                        'secret' => $tfaSecret,
                    ]
                ])], 'user_id='.intval($user['user_id']));
            }
        }

        $this->configMigration->doConfigMigration(
            $setup,
            'msp_securitysuite/twofactorauth/allow_trusted_devices',
            'msp_securitysuite_twofactorauth/google/allow_trusted_devices'
        );

        $this->configMigration->doConfigMigration(
            $setup,
            'msp_securitysuite/twofactorauth',
            'msp_securitysuite_twofactorauth/general'
        );

        // Generate random duo security key
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $this->config->saveConfig(DuoSecurity::XML_PATH_APPLICATION_KEY, $randomString, 'default', 0);
    }

    protected function upgradeTo010300(ModuleDataSetupInterface $setup)
    {
        $this->configMigration->doConfigMigration(
            $setup,
            'msp_securitysuite_twofactorauth/general/force_provider',
            'msp_securitysuite_twofactorauth/general/force_provider_0'
        );
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->upgradeTo010200($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0') < 0) {
            $this->upgradeTo010300($setup);
        }

        $setup->endSetup();
    }
}
