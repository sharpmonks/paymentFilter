<?php

namespace SharpMonks\PaymentFilter\Observer;;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SharpMonks\PaymentFilter\Helper\Data;

/**
 * Class catalogProductSaveBefore
 */
class CatalogProductSaveBefore implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Standard constructor.
     * @param Http $request
     * @param Data $dataHelper
     */
    public function __construct(
        Http $request,
        Data $dataHelper
    ) {
        $this->request = $request;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Initialize the payment methods attribute value with an array if it is
     * empty.
     * If we don't do this we cannot deselect all payment methods for a product.
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->dataHelper->moduleActive()) {
            return;
        }

        $product = $observer->getEvent()->getProduct();
        $params = $this->request->getParam('product');
        if (!isset($params['product_payment_methods'])) {
            $product->setProductPaymentMethods(array());
        }
    }
}
