<?php

namespace App\Entity;

use App\VO\Email;
use App\VO\Password;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 26, unique: true)]
    private string $id;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    private function __construct(string $email, string $password, array $roles = ['ROLE_USER'])
    {
        $this->id = Ulid::generate();
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public static function fromValues(string $email, string $password, array $roles = ['ROLE_USER']): self
    {
        return new self($email, $password, $roles);
    }

    public static function fromValueObjects(Email $email, Password $password, array $roles = ['ROLE_USER']): self
    {
        return new self($email->getValue(), $password->getValue(), $roles);
    }


    /**
     * Obtener el identificador único del usuario.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Obtener el email del usuario.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Establecer el email del usuario.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Obtener la contraseña del usuario.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Establecer la contraseña del usuario.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Obtener los roles del usuario.
     */
    public function getRoles(): array
    {
        // Garantizar que siempre haya al menos un rol predeterminado
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Establecer los roles del usuario.
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Eliminar datos sensibles.
     */
    public function eraseCredentials(): void
    {
        // Este método se puede usar para limpiar datos temporales sensibles
    }

    /**
     * Devuelve el identificador único del usuario (en este caso, el email).
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
