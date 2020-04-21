<?php

namespace SharpMonks\PaymentFilter\Observer;;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use SharpMonks\PaymentFilter\Helper\Data;

class paymentMethodIsActive implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Standard constructor.
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Data $dataHelper
    ) {
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Un-serialize the methods array.
     * Called in adminhtml and frontend area.
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->dataHelper->moduleActive()) {
            return;
        }

        $checkResult = $observer->getEvent()->getResult();
        $method = $observer->getEvent()->getMethodInstance();
        $quote = $observer->getEvent()->getQuote();

        /*
         * Check if the method is forbidden by products in the cart
         */
        if ($checkResult->getIsAvailable()) {
            if (in_array($method->getCode(), $this->dataHelper->getForbiddenPaymentMethodsForCart($quote))) {
                $checkResult->setIsAvailable(false);
            }
        }

        /*
         * Check if the method is forbidden for the customers group
         */
        if ($checkResult->getIsAvailable()) {
            $allowedPaymentMethodsForGroup = $this->dataHelper->getAllowedPaymentMethodsForCurrentGroup();
            $allowedPaymentMethodsForCustomer = $this->dataHelper->getAllowedPaymentMethodsForCustomer();
            $allowedPaymentMethods = array_merge($allowedPaymentMethodsForCustomer, $allowedPaymentMethodsForGroup);
            if (!in_array($method->getCode(), $allowedPaymentMethods)) {
                $checkResult->setIsAvailable(false);
            }
        }
    }
}
