
/*
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'mage/translate',
    'Magento_Catalog/js/price-utils'
], function ($, ko, Component, url, quote, shippingService, pickupRegistry, t, priceUtils) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Tiargsa_CorreoArgentino/checkout/pickup/pickup-correo'
        },

        initialize: function (config) {
            this.stores = ko.observableArray();
            this.stores(Object.keys(checkoutConfig.correo.stores));
            this.selectedStore = ko.observable();
            this.correoErrorMessage = ko.observable();
            this._super();
        },

        initObservable: function () {
            this._super();

            this.showStoreSection = ko.computed(function () {
                return this.stores().length !== 0
            }, this);

            this.selectedMethod = ko.computed(function () {
                var method = quote.shippingMethod();
                return method != null ? method.carrier_code + '_' + method.method_code : null;
            }, this);

            return this;
        },
    });
});
