/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../../model/shipping-rates-validator/correo',
    '../../model/shipping-rates-validation-rules/correo'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    correoShippingRatesValidator,
    correoShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('correoestandar', correoShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('correoestandar', correoShippingRatesValidationRules);

    defaultShippingRatesValidator.registerValidator('correosucursal', correoShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('correosucursal', correoShippingRatesValidationRules);

    defaultShippingRatesValidator.registerValidator('correourgente', correoShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('correourgente', correoShippingRatesValidationRules);

    return Component;
});
