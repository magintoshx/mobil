<?php

require_once('app/inc/iyzipay/IyzipayBootstrap.php');

IyzipayBootstrap::init();

class Config
{
    public static function options()
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey("kZBYKYFiL1P1KgSlrv9n489H7J5EjO9O");
        //$options->setApiKey("sandbox-l7e2LOzlfUZ2oNm20ccB5mL0kP32bUVR");
        $options->setSecretKey("RgWKSMWP10EEdp4xwiEWkhii0dlXyL6v");
        //$options->setSecretKey("sandbox-OWpETIgP6M4yDSgRoMDbdukCC1mP9Iqp");
        $options->setBaseUrl("https://api.iyzipay.com");
        //$options->setBaseUrl("https://sandbox-api.iyzipay.com");
        return $options;
    }
}