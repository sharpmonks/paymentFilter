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

namespace SharpMonks\PaymentFilter\Plugin;

use Magento\Backend\Model\Session;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use SharpMonks\PaymentFilter\Helper\Data;
use SharpMonks\PaymentFilter\Model\Config\Source\Payment\Methods;

/**
 * Adminhtml extension customer groups edit form block
 *
 * @category   SharpMonks
 * @package    SharpMonks_PaymentFilter
 * @author     Sharp Monks <sharpmonks.official@gmail.com>
 */
class Form extends Methods
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var OptionFactory
     */
    protected $eavResourceModelEntityAttributeOptionFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Group
     */
    private $_customerGroup;

    /**
     * @var GroupFactory
     */
    protected $customerGroupFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        OptionFactory $eavResourceModelEntityAttributeOptionFactory,
        Session $backendSession,
        Data $dataHelper,
        Http $request,
        GroupFactory $customerGroupFactory
    ) {
        $this->storeManager = $storeManager;
        $this->eavResourceModelEntityAttributeOptionFactory = $eavResourceModelEntityAttributeOptionFactory;
        $this->backendSession = $backendSession;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->customerGroupFactory = $customerGroupFactory;
        parent::__construct($storeManager, $eavResourceModelEntityAttributeOptionFactory, $dataHelper);
    }

    /**
     * Extend form for rendering the payment method multi select
     * @param \Magento\Customer\Block\Adminhtml\Group\Edit\Form $forms
     * @return \Magento\Framework\Data\Form
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function aftersetForm(\Magento\Customer\Block\Adminhtml\Group\Edit\Form $forms)
    {
        $form = $forms->getForm();
        /*
         * Remember the posted form value, because parent::_prepareLayout() might
         * set them to null after assigning to form.
         */
        if ($this->dataHelper->moduleActive()) {
            if ($this->backendSession->getCustomerGroupData()) {
                $values = $this->backendSession->getCustomerGroupData();
            } else {
                $values = $this->getCurrentCustomerGroup()->getData();
            }
            $value = isset($values['allowed_payment_methods']) ? $values['allowed_payment_methods'] : array();

            /*
             * Add payment method multi select and set value
             */
            $fieldset = $form->addFieldset('payment_fieldset', array(
                'legend' => __('Group Payment Methods')
            ));

            $fieldset->addField('payment_methods_posted', 'hidden', array(
                'name' => 'payment_methods_posted',
                'value' => '1',
            ));

            $fieldset->addField('payment_methods', 'multiselect', array(
                'name' => 'allowed_payment_methods',
                'label' => __('Payment Methods'),
                'title' => __('Payment Methods'),
                'class' => '',
                'required' => false,
                'values' => $this->toOptionArray(),
                'value' => $value,
                'after_element_html' => $this->_getPaymentComment()
            ));
        }

        return $form;
    }

    public function getCurrentCustomerGroup(){
        if (!isset($this->_customerGroup)) {
            $groupId = $this->getCustomerGroupId();
            $this->_customerGroup = $this->customerGroupFactory->create()->load($groupId);
        }

        return $this->_customerGroup;
    }

    /**
     * Return the current customer group id
     *
     * @return int|void
     */
    public function getCustomerGroupId()
    {
        if ($this->request->getParam('id')){
            return $this->request->getParam('id');
        }
        return '';
    }

    /**
     * Return the explanation for the payment methods multi select as html
     *
     * @return string
     */
    protected function _getPaymentComment()
    {
        $html = '';
        $html .= __(
            'To select multiple values, hold the Control-Key<br/>while clicking on the payment method names.'
        );
        return '<div>' . $html . '</div>';
    }
}
