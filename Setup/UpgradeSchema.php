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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
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

        $setup->endSetup();
    }
}
