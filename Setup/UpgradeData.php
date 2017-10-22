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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Json\DecoderInterface;
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
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var File
     */
    private $file;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig,
        File $file,
        Reader $moduleReader
    ) {
        $this->encoder = $encoder;
        $this->config = $config;
        $this->decoder = $decoder;
        $this->moduleReader = $moduleReader;
        $this->file = $file;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Move config from srcPath to dstPath
     * @param ModuleDataSetupInterface $setup
     * @param string $srcPath
     * @param string $dstPath
     */
    private function moveConfig(ModuleDataSetupInterface $setup, $srcPath, $dstPath)
    {
        $value = $this->scopeConfig->getValue($srcPath);

        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                $this->moveConfig($setup, $srcPath . '/' . $k, $dstPath . '/' . $k);
            }
        } else {
            $connection = $setup->getConnection();
            $configData = $setup->getTable('core_config_data');
            $connection->update($configData, ['path' => $dstPath], 'path='.$connection->quote($srcPath));
        }
    }

    private function upgradeTo010200(ModuleDataSetupInterface $setup)
    {
        $this->moveConfig(
            $setup,
            'msp_securitysuite/twofactorauth/allow_trusted_devices',
            'msp_securitysuite_twofactorauth/google/allow_trusted_devices'
        );

        $this->moveConfig(
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

    private function upgradeTo020000(ModuleDataSetupInterface $setup)
    {
        $this->moveConfig(
            $setup,
            'msp_securitysuite_twofactorauth/general/force_provider',
            'msp_securitysuite_twofactorauth/general/force_provider_0'
        );
    }

    private function upgradeTo020001(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('msp_tfa_country_codes');

        $countryCodesJsonFile =
            $this->moduleReader->getModuleDir(false, 'MSP_TwoFactorAuth') . DIRECTORY_SEPARATOR . 'Setup' .
            DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'country_codes.json';

        $countryCodesJson = $this->file->read($countryCodesJsonFile);

        $countryCodes = $this->decoder->decode(trim($countryCodesJson));

        // @codingStandardsIgnoreStart
        foreach ($countryCodes as $countryCode) {
            $connection->insert($tableName, $countryCode);
        }
        // @codingStandardsIgnoreEnd
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
            $this->upgradeTo020000($setup);
        }

        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $this->upgradeTo020001($setup);
        }

        $setup->endSetup();
    }
}
