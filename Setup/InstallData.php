<?php

namespace SharpMonks\PaymentFilter\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var
     */
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory, CustomerSetupFactory $customerSetupFactory, AttributeSetFactory $attributeSetFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'product_payment_methods',
            [
                'group' => 'Prices',
                'label' => 'Disable payment methods for this product',
                'type'  => 'varchar',
                'input' => 'multiselect',
                'source' => 'SharpMonks\PaymentFilter\Model\Config\Source\Payment\Methods',
                'backend' => 'SharpMonks\PaymentFilter\Model\Entity\Backend\Payment\Methods',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'required' => false,
                'default' => '',
                'user_defined' => 1,
                'sort_order' => 30,
                'used_in_product_listing' => true,
                'visible_on_front' => false,
                'is_filterable_in_search' => 0
            ]
        );

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'allowed_payment_methods',
            [
                'type' => 'varchar',
                'label' => 'Allow explicitly payment methods for this customer',
                'input' => 'multiselect',
                'required' => false,
                'visible' => true,
                'source' => 'SharpMonks\PaymentFilter\Model\Config\Source\Payment\Methods',
                'backend' => 'SharpMonks\PaymentFilter\Model\Entity\Backend\Payment\Methods',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'user_defined' => false,
                'is_user_defined' => false,
                'sort_order' => 1000,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'position' => 1000,
                'default' => 0,
                'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'allowed_payment_methods')
            ->addData([
                          'attribute_set_id' => $attributeSetId,
                          'attribute_group_id' => $attributeGroupId,
                          'used_in_forms' => ['adminhtml_customer'],
                      ]);

        $attribute->save();

        $setup->endSetup();
    }
}
