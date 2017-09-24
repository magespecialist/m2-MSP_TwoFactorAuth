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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $adminUserTable = $setup->getTable('admin_user');

        $connection->addColumn($adminUserTable, 'msp_tfa_enabled', [
            'type' => Table::TYPE_INTEGER,
            'length' => '1',
            'nullable' => false,
            'comment' => 'Two Factor Authentication Enabled',
        ]);

        $connection->addColumn($adminUserTable, 'msp_tfa_secret', [
            'type' => Table::TYPE_TEXT,
            'nullable' => false,
            'comment' => 'Two Factor Authentication Secret',
        ]);

        $connection->addColumn($adminUserTable, 'msp_tfa_activated', [
            'type' => Table::TYPE_INTEGER,
            'length' => '1',
            'nullable' => false,
            'comment' => 'Two Factor Authentication Activated',
        ]);

        $setup->endSetup();
    }
}
