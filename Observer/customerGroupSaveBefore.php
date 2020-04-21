<?php

namespace SharpMonks\PaymentFilter\Observer;;

use Magento\Customer\Model\Group;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use SharpMonks\PaymentFilter\Helper\Data;

class customerGroupSaveBefore implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * Standard constructor.
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     * @param Json $serialize
     * @param Data $dataHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Http $request,
        Json $serialize,
        Data $dataHelper
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->serialize = $serialize;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Un-serialize the methods array.
     * Called in adminhtml and frontend area.
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->dataHelper->moduleActive()) {
            return;
        }

        $group = $observer->getEvent()->getObject();

        /*
         * Update values
         */
        $this->_setPaymentFilterOnGroup($group);

        /*
         * Serialize array for saving
         */
        $val = $this->serialize->serialize($group->getAllowedPaymentMethods());
        $group->setAllowedPaymentMethods($val);
    }

    /**
     * Set the posted allowed payment methods on the customer group model.
     *
     * @param Group $group
     */
    protected function _setPaymentFilterOnGroup(Group $group)
    {
        if ($this->request->getParam('payment_methods_posted')) {
            $allowedPaymentMethods = $this->request->getParam('allowed_payment_methods');
            $group->setAllowedPaymentMethods($allowedPaymentMethods);
        }
    }
}
