<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserControllerTest extends WebTestCase
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

    public function testRegisterSuccess(): void
    {
        // Enviar solicitud de registro
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

        // Verificar que la respuesta sea 201
        $this->assertResponseStatusCodeSame(201);

        // Verificar que el contenido de la respuesta es JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User registered successfully', $responseData['message']);
    }

    /*public function testRegisterInvalidData(): void
    {
        // Enviar solicitud con datos inválidos
        $this->client->request(
            'POST',
            '/api/user/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([]) // Sin email ni contraseña
        );

        // Verificar que la respuesta sea 400
        $this->assertResponseStatusCodeSame(400);

        // Verificar el contenido de la respuesta
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Email and password are required.', $responseData['error']);
    }*/

    public function testRegisterDuplicateEmail(): void
    {
        // Crear un usuario previamente
        $this->client->request(
            'POST',
            '/api/user/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'securepassword'
            ])
        );

        // Enviar otra solicitud con el mismo email
        $this->client->request(
            'POST',
            '/api/user/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'anotherpassword'
            ])
        );

        // Verificar que la respuesta sea 400
        $this->assertResponseStatusCodeSame(400);

        // Verificar el contenido de la respuesta
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('User already exists.', $responseData['error']);
    }
}
