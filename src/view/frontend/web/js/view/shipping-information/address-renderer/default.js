/*******************************************************************************
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, registry, quote) {
    'use strict';

    return function (AddressRenderer) {
        return AddressRenderer.extend({
            defaults: {
                template: 'Tiargsa_CorreoArgentino/shipping-information/address-renderer/default'
            },
            getcorreoStreetAttributes: function (customAttributes) {
                var correoAttributes = ['altura','piso','departamento'];
                var attributeLabels = '';
                for(let pos in correoAttributes) {
                    let attributeCode = correoAttributes[pos];
                    if (attributeCode in customAttributes && customAttributes[attributeCode].value !== '') {
                        if(attributeCode === 'altura') {
                            attributeLabels += ' ' + customAttributes[attributeCode].value + ', ';
                        }
                        else{
                            attributeLabels += attributeCode.charAt(0).toUpperCase() + attributeCode.slice(1) + ': ' + customAttributes[attributeCode].value + ', ';
                        }
                    }
                    else if(Array.isArray(customAttributes)){
                        for(let arrayPos in customAttributes){
                            let attribute = customAttributes[arrayPos];
                            if(attributeCode === attribute.attribute_code && attribute.value !== ''){
                                if(attributeCode === 'altura') {
                                    attributeLabels += ' ' + attribute.value + ', ';
                                }
                                else{
                                    attributeLabels += attributeCode.charAt(0).toUpperCase() + attributeCode.slice(1) + ': ' + attribute.value + ', ';
                                }
                                break;
                            }
                        }
                    }
                }

                return attributeLabels.slice(0, -2);
            }
        });
    };
});
