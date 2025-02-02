<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\UserRegisterController;
use App\DTO\UserRegistrationInput;
use App\VO\Email;
use App\VO\Password;
use App\VO\Roles;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: "users")]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations            : [
        new Get(normalizationContext: ['groups' => ['user_read']]),
        new GetCollection(normalizationContext: ['groups' => ['user_read']]),
        new Put(
            normalizationContext  : ['groups' => ['user_read']],
            denormalizationContext: ['groups' => ['user_write']],
        ),
        new Delete(),
        new Post(
            uriTemplate    : '/user/register',
            formats        : ['json' => ['application/json']],
            status         : 201,
            controller     : UserRegisterController::class,
            description    : 'Registers a new user',
            input          : UserRegistrationInput::class,
            extraProperties: [
                'summary' => 'Register a new user',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string'],
                                    'password' => ['type' => 'string'],
                                ],
                                'required' => ['email', 'password'],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'User successfully registered',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ),

    ],
    normalizationContext  : ['groups' => ['user_read']],
    denormalizationContext: ['groups' => ['user_write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 26, unique: true)]
    #[Groups(['user_read'])]
    private string $id;
    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Groups(['user_read', 'user_write'])]
    private string $email;
    #[ORM\Column(type: "string")]
    private string $password;
    #[ORM\Column(type: "json")]
    #[Groups(['user_read', 'user_write'])]
    private array $roles;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $updatedAt = null;

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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
