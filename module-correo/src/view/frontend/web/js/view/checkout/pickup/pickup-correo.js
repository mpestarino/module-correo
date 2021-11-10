
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
], function ($, ko, Component, url, quote, shippingService, t, priceUtils) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Tiargsa_CorreoArgentino/checkout/pickup/pickup-correo'
        },

        initialize: function (config) {
            this.provinces = ko.observableArray();
            this.provinces(Object.keys(checkoutConfig.correo.stores));
            this.selectedProvince = ko.observable();
            this.cities = ko.observableArray();
            this.selectedCity = ko.observable();
            this.stores = ko.observableArray();
            this.selectedStore = ko.observable();
            this.correoErrorMessage = ko.observable();
            this._super();
        },

        initObservable: function () {
            this._super();

            this.showProvinceSection = ko.computed(function() {
                return this.provinces().length != 0
            }, this);
            this.showCitySection = ko.computed(function() {
                return this.cities().length != 0
            }, this);
            this.showStoreSection = ko.computed(function() {
                return this.stores().length != 0
            }, this);

            this.selectedMethod = ko.computed(function() {
                var method = quote.shippingMethod();
                return method != null ? method.carrier_code + '_' + method.method_code : null;
            }, this);

            return this;
        },


        getCotizacionStore:function(){
            storeService.getCotizacionStore(quote.shippingAddress(), this);
        },

        provinceChange: function(obj, event){
            if(this.selectedProvince() && this.selectedProvince() in checkoutConfig.correo.stores) {
                this.cities(Object.keys(checkoutConfig.correo.stores[this.selectedProvince()]));
            }
            else{
                this.cities([]);
            }
            this.selectedCity(null);
        },
        cityChange: function(obj, event){
            if(this.selectedProvince() && this.selectedCity() && this.selectedProvince() in checkoutConfig.correo.stores && this.selectedCity() in checkoutConfig.correo.stores[this.selectedProvince()]) {
                this.stores(Object.keys(checkoutConfig.correo.stores[this.selectedProvince()][this.selectedCity()])); //borro las stores
            }
            else{
                this.stores([]);
            }
            this.selectedStore(null);
        },
        storeChange: async function (obj, event) {
            if (this.selectedStore()){
                var self = this;
                $.ajax({
                    url: url.build('correo/checkout/pickuprates'),
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        store_id: checkoutConfig.correo.stores[this.selectedProvince()][this.selectedCity()][this.selectedStore()].codigo,
                        store_name: this.selectedStore(),
                        quote_id: quote.getQuoteId(),
                        address_zip: quote.shippingAddress().postcode
                    },
                    complete: function (response) {
                        if(response.status == 200 && response.responseJSON.status){
                            let costoEnvio = priceUtils.formatPrice(response.responseJSON.price, quote.getPriceFormat());
                            jQuery('#label_method_sucursal_CorreoArgentinosucursal').siblings('.col-price').children('span').text(costoEnvio);
                            self.correoErrorMessage('');
                        }
                        else{
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
