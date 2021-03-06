<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Plugin\Quote\Model\Quote\Address;

class BillingAddressPersister
{
    public function beforeSave(
        \Magento\Quote\Model\Quote\Address\BillingAddressPersister $subject,
        $quote,
        \Magento\Quote\Api\Data\AddressInterface $address,
        $useForShipping = false
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
                $logMessage = "Method: BillingAddressPersister::beforeSave\n";
                $logMessage .= "Message: " . $e->getMessage() . "\n";
                \Tiargsa\CorreoArgentino\Helper\Data::log($logMessage, 'correo_attribute_errors_' . date('Y_m') . '.log');
            }
        }
    }
}
