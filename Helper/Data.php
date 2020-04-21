<?php
/**
 * Magento
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 * @category   SharpMonks
 * @package    SharpMonks_PaymentFilter
 * @copyright  Copyright (c) 2020 Prince Antil https://sharpmonks.com/
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace SharpMonks\PaymentFilter\Helper;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Customer Group Payment Methods Helper
 * @category    SharpMonks
 * @package     SharpMonks_PaymentFilter
 * @author      Prince Antil <sharpmonks.official@gmail.com>
 */
class Data extends AbstractHelper
{
    const EXPLANATION_URL = 'https://github.com/SharpMonks/PaymentFilter/issues/19';

    /**
     * @var string[]
     */
    protected $_forbiddenPaymentMethodsForCart;

    /** @var  Quote */
    protected $_quote;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var GroupFactory
     */
    protected $customerGroupFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Cart
     */
    protected $checkoutCart;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Group
     */
    private $_customerGroup;
    /**
     * @var Customer
     */
    private $_customer;

    public function __construct(
        Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        Session $customerSession,
        GroupFactory $customerGroupFactory,
        LoggerInterface $logger,
        Cart $checkoutCart,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->paymentHelper = $paymentHelper;
        $this->customerSession = $customerSession;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->logger = $logger;
        $this->checkoutCart = $checkoutCart;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context
        );
    }


    /**
     * Fetch all configured payment methods for the given store (0 = global
     * config scope) as an options array for select widgets.
     * @param integer $storeId
     * @param Quote $quote
     * @return array
     */
    public function getPaymentMethodOptions(
        $storeId,
        $quote = null)
    {
        return $this->paymentHelper->getPaymentMethodList(
            true,
            true,
            true);
    }

    /**
     * Return the forbidden payment method codes in an array for the current cart items.
     * @param Quote|null $quote The current quote
     * @return array
     */
    public function getForbiddenPaymentMethodsForCart(Quote $quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getCurrentQuote();
        }
        if (null === $this->_forbiddenPaymentMethodsForCart) {
            $methods = array();
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                $productPaymentMethods = $this->getForbiddenPaymentMethodsFromProduct($item->getProduct());
                if (!$productPaymentMethods) {
                    continue;
                }

                foreach ($productPaymentMethods as $method) {
                    if (
                    !in_array(
                        $method,
                        $methods)) {
                        $methods[] = $method;
                    }
                }
            }
            $this->_forbiddenPaymentMethodsForCart = $methods;
        }

        return $this->_forbiddenPaymentMethodsForCart;
    }

    /**
     * Return the current quote based on the customer session and log a
     * self-explanatory warning.
     * @return Quote
     */
    public function getCurrentQuote()
    {
        $this->logger->log(
            \Monolog\Logger::NOTICE,
            sprintf(
                '%s: Loading quote from session. If this line floods the logs
                 we are in _afterLoad of a cart being loaded. See: %s',
                __CLASS__,
                self::EXPLANATION_URL));
        if (!isset($this->_quote))
            $this->_quote = $this->checkoutCart->getQuote();

        return $this->_quote;
    }

    /**
     * Return the payment methods that are configured as forbidden for the given product
     * @param Product $product
     * @return array
     */
    public function getForbiddenPaymentMethodsFromProduct(Product $product)
    {
        $productPaymentMethods = $product->getProductPaymentMethods();

        if (!is_array($productPaymentMethods)) {
            $productPaymentMethods = explode(
                ',',
                (string)$productPaymentMethods);
        }

        return $productPaymentMethods;
    }

    /**
     * Return the allowed payment method codes for the current customer group.
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllowedPaymentMethodsForCurrentGroup()
    {
        return (array)$this->getCurrentCustomerGroup()->getAllowedPaymentMethods();
    }

    /**
     * Return the current customer group. If the customer is not logged in, the NOT LOGGED IN group is returned.
     * This is different from the default group configured in system > config > customer.
     * @return Group
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCurrentCustomerGroup()
    {
        if (!isset($this->_customerGroup)) {
            $groupId = $this->customerSession->getCustomerGroupId();
            $this->_customerGroup = $this->customerGroupFactory->create()->load($groupId);
        }

        return $this->_customerGroup;
    }

    /**
     * Return the allowed payment method codes for the current customer
     * @return array
     */
    public function getAllowedPaymentMethodsForCustomer()
    {
        return (array)$this->getCurrentCustomer()->getAllowedPaymentMethods();
    }

    /**
     * Return the current customer, if the customer is logged in
     * @return Customer
     */
    public function getCurrentCustomer()
    {
        if (!isset($this->_customer)) {
            $this->_customer = $this->customerSession->getCustomer();
        }

        return $this->_customer;
    }

    /**
     * Check if the extension has been disabled in the system configuration
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function moduleActive()
    {
        return !(bool)$this->getConfig('disable_ext');
    }

    /**
     * Return the config value for the passed key (current store)
     * @param string $key
     * @return string
     * @throws NoSuchEntityException
     */
    public function getConfig($key)
    {
        $path = 'checkout/payfilter/' . $key;

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore());
    }

}
