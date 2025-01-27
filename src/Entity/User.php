<?php

namespace App\Entity;

use App\VO\Email;
use App\VO\Password;
use App\VO\Roles;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 26, unique: true)]
    private string $id;
    #[ORM\Column(type: "string", length: 180, unique: true)]
    private string $email;
    #[ORM\Column(type: "string")]
    private string $password;
    #[ORM\Column(type: "json")]
    private array $roles;

    private function __construct(string $email, string $password, Roles $roles)
    {
        $this->id = Ulid::generate();
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles->getValue();
    }

    public static function fromValues(string $email, string $password, array $roles = []): self
    {
        return new self($email, $password, new Roles($roles));
    }

    public static function fromValueObjects(Email $email, Password $password, Roles $roles): self
    {
        return new self($email->getValue(), $password->getValue(), $roles);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email->getValue();
        return $this;
    }

    public function setPassword(Password $password): self
    {
        $this->password = $password->getValue();
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(Roles $roles): self
    {
        $this->roles = $roles->getValue();
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
        // algo
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
