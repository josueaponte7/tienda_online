<?php

use Behat\Behat\Context\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeatureContext extends WebTestCase implements Context
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // Aquí puedes agregar tus pasos
    /**
     * @Given I am logged in as a user
     */
    public function iAmLoggedInAsAUser()
    {
        // Aquí puedes interactuar con tu aplicación de Symfony sin necesidad de Mink
        $client = static::createClient();
        $client->request('GET', '/login');
        // Realiza la acción de login o cualquier otra interacción que necesites
    }
}
