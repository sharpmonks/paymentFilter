<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SharpMonks\PaymentFilter\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\GroupRegistry;
use Magento\Framework\App\Request\Http;

class GroupRetrieve
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    public function __construct(
        Http $request,
        GroupFactory $groupFactory
    ) {
        $this->request = $request;
        $this->groupFactory = $groupFactory;
    }

    public function aroundRetrieve(
        GroupRegistry $subject,
        callable $proceed,
        $groupId)
    {
        /** @var GroupInterface $group */
        $groupModel = $this->groupFactory->create()->load($groupId);

        if ($this->request->getParam('payment_methods_posted')) {
            $allowedPaymentMethods = $this->request->getParam('allowed_payment_methods');
            $groupModel->setAllowedPaymentMethods($allowedPaymentMethods);
        }

        return $groupModel;
    }

}
