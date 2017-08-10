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

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    protected function upgradeTo010100(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('msp_tfa_trusted');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'msp_tfa_trusted_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Trusted device ID'
            )
            ->addColumn(
                'date_time',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Date and time'
            )
            ->addColumn(
                'user_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'User ID'
            )
            ->addColumn(
                'device_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Device name'
            )
            ->addColumn(
                'token',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Token'
            )
            ->addColumn(
                'last_ip',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Last IP'
            )
            ->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('msp_tfa_trusted'),
                    'user_id',
                    $setup->getTable('admin_user'),
                    'user_id'
                ),
                'user_id',
                $setup->getTable('admin_user'),
                'user_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }

    protected function upgradeTo010200(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $adminUserTable = $setup->getTable('admin_user');

        $connection->changeColumn($adminUserTable, 'msp_tfa_enabled', 'msp_tfa_provider', [
            'type' => Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Two Factor Authentication Provider',
        ]);

        $connection->changeColumn($adminUserTable, 'msp_tfa_secret', 'msp_tfa_config', [
            'type' => Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Two Factor Authentication Config',
        ]);
    }

    protected function upgradeTo010300(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tfaAdminUserTable = $setup->getTable('msp_tfa_user_config');
        $adminUserTable = $connection->getTableName('admin_user');

        $table = $setup->getConnection()
            ->newTable($tfaAdminUserTable)
            ->addColumn(
                'msp_tfa_user_config_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'TFA admin user ID'
            )
            ->addColumn(
                'user_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'User ID'
            )
            ->addColumn(
                'encoded_providers',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Encoded providers list'
            )
            ->addColumn(
                'encoded_config',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Encoded providers configuration'
            )
            ->addColumn(
                'default_provider',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Default provider'
            )
            ->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('msp_tfa_user_config'),
                    'user_id',
                    $setup->getTable('admin_user'),
                    'user_id'
                ),
                'user_id',
                $setup->getTable('admin_user'),
                'user_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            );

        $connection->createTable($table);

        // Migrate data from old configuration
        $users = $connection->fetchAll($connection->select()->from($adminUserTable));
        foreach ($users as $user) {
            try {
                $providerConfig = $this->decoder->decode($user['msp_tfa_config']);
                if (isset($providerConfig[$user['msp_tfa_provider']])) {
                    $providerConfig[$user['msp_tfa_provider']]['active'] = $user['msp_tfa_activated'];
                }
            } catch (\Exception $e) {
                $providerConfig = [];
            }

            $providerCode = $user['msp_tfa_provider'];
            if ($providerCode == 'none') {
                $providerCode = '';
            }

            $connection->insert($tfaAdminUserTable, [
                'user_id' => $user['user_id'],
                'encoded_config' => $this->encoder->encode($providerConfig),
                'encoded_providers' => $this->encoder->encode([$providerCode]),
            ]);
        }

        $connection->dropColumn($adminUserTable, 'msp_tfa_provider');
        $connection->dropColumn($adminUserTable, 'msp_tfa_config');
        $connection->dropColumn($adminUserTable, 'msp_tfa_activated');
    }

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $this->upgradeTo010100($setup);
        }

        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->upgradeTo010200($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0') < 0) {
            $this->upgradeTo010300($setup);
        }

        $setup->endSetup();
    }
}
