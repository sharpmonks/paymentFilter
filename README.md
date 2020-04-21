# SharpMonks PaymentFilter Magento2 Extension
SharpMonks Magento 2 paymentFilter Extensions for Products, Customers and Customer Groups

This module enables you to select which payment methods are available for every customer and customer group. Also, Payment methods can be disabled for specific products. 

A customer can only use the payment methods during checkout available to his (customer group OR himself) AND not disabled for the products in the shopping cart.

<b>This module is the Magento 2 version of this Magento 1 module</b> https://github.com/riconeitzel/PaymentFilter 
# Compatibility
>This extension supports Magento 2.2.x and 2.3.x.

# Points to remember
After installing this extension you have to configure the payment methods available to each customer group. 

You can do that in the admin interface under Customers > Customer Groups. The default is NONE, so if you don't do that NO payment methods will be available and customers will not be able to check out.

The default for products is to allow ALL payment methods, so you only have to configure the payment methods available to every group. Only change the product level payment method configuration if you want to disable one or more payment method for a specific products.
# Disable Extension
The whole extension can be disabled in "Stores > Configuration > Sales > Checkout" on a Global or Website scope.

# Composer Installation
> composer require sharpMonks/module-payment-filter
 
# Uninstall

If you ever uninstall the extension (I don't hope so :)) your site will be broken, because Magento doesn't support database updates on uninstalls to remove attributes.

To fix the error, execute the following SQL:

>DELETE FROM `eav_attribute` WHERE attribute_code = 'product_payment_methods';

>DELETE FROM `setup` WHERE code = 'payfilter_setup';

>ALTER TABLE `customer_group` DROP `allowed_payment_methods`;

>IMPORTANT! Then clear the magento cache.

# Maintainer

If you have ideas for improvements or find bugs, submit here: https://github.com/sharpmonks/paymentFilter/issues

Create pull requests and I'll review them to approve for merge.
# License

This module is licensed under OSL-3.0