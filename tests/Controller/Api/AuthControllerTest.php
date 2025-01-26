<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear cliente y servicios necesarios
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        // Limpiar la base de datos de pruebas
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Crear un usuario de prueba
        $user = new User();
        $user->setEmail('test@example.com');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'securepassword');
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function testLoginSuccess(): void
    {
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

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);
    }

    public function testLoginFailure(): void
    {
        $this->client->request(
            'POST',
            '/api/user/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword'
            ])
        );

        $this->assertResponseStatusCodeSame(401);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(['error' => 'Invalid credentials.'], $responseData);
    }
}
