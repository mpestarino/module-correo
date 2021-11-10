<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Controller\Adminhtml\Rate;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Tiargsa_CorreoArgentino::rate_operations';

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Tiargsa_CorreoArgentino::rate_operations')
            ->getConfig()->getTitle()->prepend(__('Gestion de tarifas'));

        return $resultPage;
    }


}
