<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DocumentType implements OptionSourceInterface
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
