<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Plugin\Quote\Model;


class ShippingAddressManagement
{
    public function beforeAssign(
        \Magento\Quote\Model\ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {

        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            try {
                $address->setDni($extAttributes->getDni());
                $address->setAltura($extAttributes->getAltura());
                $address->setPiso($extAttributes->getPiso());
                $address->setDepartamento($extAttributes->getDepartamento());
                $address->setCelular($extAttributes->getCelular());
                $address->setObservaciones($extAttributes->getObservaciones());
            } catch (\Exception $e) {
                $logMessage = "Method: ShippingAddressManagement::beforeAssign\n";
                $logMessage .= "Message: " . $e->getMessage() . "\n";
                \Tiargsa\CorreoArgentino\Helper\Data::log($logMessage, 'correo_attribute_errors_' . date('Y_m') . '.log');
            }
        }
    }
}
