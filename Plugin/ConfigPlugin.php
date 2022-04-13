<?php

namespace Tiargsa\CorreoArgentino\Plugin;

use Magento\Config\Model\Config;
use Magento\Framework\Message\ManagerInterface;
use Tiargsa\CorreoArgentino\Service\CorreoApiService;

class ConfigPlugin
{

    protected $correoApiService;

    protected $messageManager;

    public function __construct(
        CorreoApiService $correoApiService,
        ManagerInterface $messageManager
    )
    {
        $this->correoApiService = $correoApiService;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Config $subject
     */
    public function afterSave(
        Config $subject
    ) {

        $login = json_decode($this->correoApiService->login(), true);

        if ($login != null){
            $this->messageManager->getMessages(true);
            return $this->messageManager->addWarningMessage('Usuario o Contraseña invalido');
        }

    }
}
