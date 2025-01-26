<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AuthFlowTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear cliente y servicios necesarios
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // Limpiar la base de datos de pruebas
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testRegisterAndLoginFlow(): void
    {
        // 1. Registrar un nuevo usuario
        $this->client->request(
            'POST',
            '/api/user/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
                'password' => 'securepassword'
            ])
        );

        // Validar registro exitoso
        $this->assertResponseStatusCodeSame(201);

        $registerResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $registerResponse);
        $this->assertEquals('User registered successfully', $registerResponse['message']);
        $this->assertArrayHasKey('token', $registerResponse);
        $this->assertNotEmpty($registerResponse['token']);

        $token = $registerResponse['token'];

        // 2. Intentar login con las credenciales registradas
        $this->client->request(
            'POST',
            '/api/user/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
                'password' => 'securepassword'
            ])
        );

        // Validar login exitoso
        $this->assertResponseStatusCodeSame(200);

        $loginResponse = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $loginResponse);
        $this->assertNotEmpty($loginResponse['token']);

        $loginToken = $loginResponse['token'];

        // 3. Validar el acceso a una ruta protegida con el token del login
        $this->client->request(
            'GET',
            '/api/protected',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $loginToken]
        );

        // Validar acceso a la ruta protegida
        $this->assertResponseStatusCodeSame(200);

        $protectedResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $protectedResponse);
        $this->assertEquals('Welcome to the protected route!', $protectedResponse['message']);
        $this->assertEquals('test@example.com', $protectedResponse['user']);
    }
}
