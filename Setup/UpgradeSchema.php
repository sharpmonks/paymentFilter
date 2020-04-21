<?php

namespace SharpMonks\PaymentFilter\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<=')) {
            $setup->getConnection()->addColumn(
                $setup->getTable( 'customer_group'),
                    'allowed_payment_methods',
                    [
                      'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                      'length' => 255,
                      'nullable' => true,
                      'comment' => 'Allowed Payment Methods'
                    ]
              );
        }

        $setup->endSetup();
    }
}
