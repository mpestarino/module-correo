
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

        storeChange: async function (obj, event) {
            if (this.selectedStore()) {
                console.log('barraaaacaaaas');
                var self = this;
                $.ajax({
                    url: url.build('correo/checkout/pickuprates'),
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        //store_id: checkoutConfig.correo.stores[this.selectedStore()].codigo,
                        store_name: this.selectedStore(),
                        quote_id: quote.getQuoteId(),
                        address_zip: quote.shippingAddress().postcode
                    },
                    complete: function (response) {
                        if (response.status === 200 && response.responseJSON.status) {
                            //let costoEnvio = priceUtils.formatPrice(response.responseJSON.price, quote.getPriceFormat());
                            //jQuery('#label_method_sucursal_correosucursal').siblings('.col-price').children('span').text(costoEnvio);
                            self.correoErrorMessage('');
                        } else {
                            self.correoErrorMessage('No se encontraron cotizaciones para la sucursal seleccionada');
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                    }
                });
            }
        },
    });
});
