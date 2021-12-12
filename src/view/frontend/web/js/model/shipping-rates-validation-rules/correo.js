/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

define([], function() {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'country_id': {
                    'required': true
                },
                'postcode': {
                    'required': true
                }
            };
        }
    };
});
