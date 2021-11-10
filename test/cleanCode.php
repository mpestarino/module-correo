<?php

class MailSender 
{
    /**
     * @throws SendException
     */
    public function send (MailAddress $address) 
    {
        mail($address->toString());
    }
}

class MailAddress
{
    public function __construct($address)
    {
        $this->address = $address;

        if (!$address->isValid()) {
            throw new SendException();
        }
    }

    public function isValid()
    {
        return true;
    }

    public function toString()
    {
        return $this->address;
    }
}

class MailAddressException extends Exception 
{
    
}


$mailSender = new MailSender();
try {
    $mailAddress = new MailAddress("diego.gutman85@gmail.com");
    $mailSender->send("diego.gutman85@gmail.com");
} catch (SendException $exception) {
    echo $exception->getMessage();
}
