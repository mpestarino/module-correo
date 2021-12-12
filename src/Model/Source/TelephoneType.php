<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TelephoneType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            1    => 'Trabajo',
            2     => 'Celular',
            3     => 'Casa',
            4     => 'Otros',
        ];
    }
}
