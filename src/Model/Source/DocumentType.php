<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Source;

class DocumentType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            'DNI'    => 'DNI',
            'CUIT'     => 'CUIT',
            'CUIL'     => 'CUIL',
        ];
    }
}
