/*******************************************************************************
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'ko',
    'mage/translate',
], function ($, wrapper, quote,ko,$t) {
    'use strict';

    return function (targetModule) {
        return targetModule.extend({
            validateShippingInformation: function () {
                let result = this._super();
                if(result && quote.shippingMethod()) {
                    let method = quote.shippingMethod().method_code;
                    let code = quote.shippingMethod().carrier_code;

                    if (code == 'correosucursal' && method == 'sucursal') {
                        let optionProvincia = $("#correosucursal-province-list").children("option:selected").val();
                        let indexProvincia = $("#correosucursal-province-list").prop('selectedIndex');
                        let optionLocalidad = $("#correosucursal-city-list").children("option:selected").val();
                        let indexLocalidad = $("#correosucursal-city-list").prop('selectedIndex');
                        let optionSucursal = $("#correosucursal-store-list").children("option:selected").val();
                        let indexSucursal = $("#correosucursal-store-list").prop('selectedIndex');

                        if (optionProvincia == undefined || indexProvincia == undefined || optionProvincia == "" || indexProvincia == 0) {
                            this.errorValidationMessage(
                                $t('Seleccione una provincia para continuar')
                            );
                            result = false;
                        }
                        else if (optionLocalidad == undefined || indexLocalidad == undefined || optionLocalidad == "" || indexLocalidad == 0) {
                            this.errorValidationMessage(
                                $t('Seleccione una localidad para continuar')
                            );
                            result = false;
                        }
                        else if (optionSucursal == undefined || indexSucursal == undefined || optionSucursal == "" || indexSucursal == 0) {
                            this.errorValidationMessage(
                                $t('Seleccione una sucursal para continuar')
                            );
                            result = false;
                        }
                    }
                }
                return result;
            }
        });
    };
});