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

namespace SharpMonks\PaymentFilter\Model\Entity\Backend\Payment;

use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;

/**
 * Backend model for attribute with multiple values, SharpMonks_PaymentFilter version
 * @category   SharpMonks
 * @package    SharpMonks_PaymentFilter
 * @author     Sharp Monks <sharpmonks.official@gmail.com>
 */
class Methods extends ArrayBackend
{
    public function beforeSave($object)
    {
        $data = $object->getData($this->getAttribute()->getAttributeCode());

        if (!isset($data)) {
            $data = array();
        } elseif (is_string($data)) {
            $data = explode(',', $data);
        } elseif (!is_array($data)) {
            $data = array();
        }

        $object->setData($this->getAttribute()->getAttributeCode(), $data);

        /**
         * Mage_Eav_Model_Entity_Attribute_Backend_Array::beforeSave() makes a string from the array values
         */
        return parent::beforeSave($object);
    }
}
