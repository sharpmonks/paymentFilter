<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   SharpMonks
 * @package    SharpMonks_PaymentFilter
 * @copyright  Copyright (c) 2020 Prince Antil https://sharpmonks.com/
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace SharpMonks\PaymentFilter\Model\Config\Source\Payment;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\Db\Select;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use SharpMonks\PaymentFilter\Helper\Data;

class Methods extends AbstractSource
{
    protected $_options;

    protected $_storeCode = Store::ADMIN_CODE;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var OptionFactory
     */
    protected $eavResourceModelEntityAttributeOptionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        OptionFactory $eavResourceModelEntityAttributeOptionFactory,
        Data $dataHelper
    ) {
        $this->storeManager = $storeManager;
        $this->eavResourceModelEntityAttributeOptionFactory = $eavResourceModelEntityAttributeOptionFactory;
        $this->dataHelper = $dataHelper;
    }
    public function toOptionArray()
    {
        if (!$this->_options) {
            $store = $this->storeManager->getStore($this->_storeCode);
            $this->_options = $this->dataHelper->getPaymentMethodOptions($store->getId());
        }
        return $this->_options;
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Get a text for option value
     * @param string|integer $value
     * @return bool|array
     */
    public function getOptionText($value)
    {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $options = $this->getAllOptions();

        if ($isMultiple) {
            $values = array();
            foreach ($options as $item) {
                if (in_array($item['value'], $value)) {
                    $values[] = $item['label'];
                }
            }
            return $values;
        } else {
            foreach ($options as $item) {
                if ($item['value'] == $value) {
                    return $item['label'];
                }
            }
            return false;
        }
    }

    /**
     * Retrieve Indexes for Flat Catalog
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = array(
            'type' => 'index',
            'fields' => array($this->getAttribute()->getAttributeCode())
        );

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->eavResourceModelEntityAttributeOptionFactory->create()
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
