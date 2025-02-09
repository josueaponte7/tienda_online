<?php

declare(strict_types=1);

namespace App\Tests\Api\Request;

use App\Request\Api\UserRegisterRequest;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class UserRegisterRequestTest extends TestCase
{
    /**
     * Prueba que un JSON inválido en la solicitud lanza una JsonException.
     *
     * @return void
     * @throws JsonException
     */
    public function testInvalidJsonThrowsException(): void
    {
        // Crear una solicitud HTTP con un JSON inválido
        $invalidJsonContent = '{email: "invalid-email", password: 123';  // Falta cerrar la llave y las comillas en la clave 'email'
        $request = new Request([], [], [], [], [], [], $invalidJsonContent);

        // Esperar que se lance una JsonException
        $this->expectException(JsonException::class);

        // Ejecutar el método fromRequest con la solicitud inválida
        UserRegisterRequest::fromRequest($request);
    }

    /**
     * Prueba que la falta de email en la solicitud lanza una InvalidArgumentException.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function testMissingEmailThrowsException(): void
    {
        $requestContent = json_encode(['password' => 'securepassword'], JSON_THROW_ON_ERROR);
        $request = new Request([], [], [], [], [], [], $requestContent);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email es obligatorio, y debe ser una cadena de texto.');

        UserRegisterRequest::fromRequest($request);
    }

    /**
     * Prueba que la falta de password en la solicitud lanza una InvalidArgumentException.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function testMissingPasswordThrowsException(): void
    {
        $requestContent = json_encode(['email' => 'test@example.com'], JSON_THROW_ON_ERROR);
        $request = new Request([], [], [], [], [], [], $requestContent);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password es obligatorio.');

        UserRegisterRequest::fromRequest($request);
    }

    /**
     * Prueba que un JSON válido en la solicitud crea una instancia de UserRegisterRequest.
     *
     * @return void
     */
    public function testValidJsonCreatesInstance(): void
    {
        $requestContent = json_encode([
            'email' => 'test@example.com',
            'password' => 'securepassword',
            'roles' => 'ROLE_USER,ROLE_ADMIN',
        ], JSON_THROW_ON_ERROR);
        $request = new Request([], [], [], [], [], [], $requestContent);

        $registerRequest = UserRegisterRequest::fromRequest($request);

        $this->assertInstanceOf(UserRegisterRequest::class, $registerRequest);
        $this->assertEquals('test@example.com', $registerRequest->getEmail());
        $this->assertEquals('securepassword', $registerRequest->getPassword());
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $registerRequest->getRoles());
    }

    /**
     * Prueba que el campo roles en la solicitud se transforma en un array.
     *
     * @return void
     */
    public function testRolesFieldIsTransformedToArray(): void
    {
        $requestContent = json_encode([
            'email' => 'test@example.com',
            'password' => 'securepassword',
            'roles' => 'ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN',
        ], JSON_THROW_ON_ERROR);
        $request = new Request([], [], [], [], [], [], $requestContent);

        $registerRequest = UserRegisterRequest::fromRequest($request);

        $expectedRoles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
        $this->assertEquals($expectedRoles, $registerRequest->getRoles());
    }
}
