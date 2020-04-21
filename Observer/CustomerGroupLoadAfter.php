<?php

namespace SharpMonks\PaymentFilter\Observer;;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SharpMonks\PaymentFilter\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

class CustomerGroupLoadAfter implements ObserverInterface
{
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
     * @param Data $dataHelper
     * @param Json $serialize
     */
    public function __construct(
        Data $dataHelper,
        Json $serialize
    ) {
        $this->dataHelper = $dataHelper;
        $this->serialize = $serialize;
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

        if (is_string($group->getAllowedPaymentMethods())) {
            $val = $this->serialize->unserialize($group->getAllowedPaymentMethods());
            $group->setAllowedPaymentMethods($val);
        }
    }
}
