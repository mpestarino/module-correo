define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, registry, quote) {
    'use strict';

    return function (ShippingInformation) {
        return ShippingInformation.extend({
            getShippingMethodTitle: function () {
                var shippingMethodTitle = this._super(),
                    shippingMethod = quote.shippingMethod();

                if (shippingMethod && shippingMethod['carrier_code'] === 'correosucursal') {
                    shippingMethodTitle = shippingMethodTitle + ': ' + $('#correosucursal-store-list').val();
                }

                return shippingMethodTitle;
            }
        });
    };
});
