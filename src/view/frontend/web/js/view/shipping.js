/*******************************************************************************
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
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

                    if (code === 'correosucursal') {
                        let optionSucursal = $("#correosucursal-store-list").children("option:selected").val();
                        let indexSucursal = $("#correosucursal-store-list").prop('selectedIndex');
                        if (optionSucursal === undefined || indexSucursal === undefined || optionSucursal == "" || indexSucursal == 0) {
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
