<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class UserRegister
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private string $userId;

    #[MongoDB\Field(type: 'string')]
    private string $email;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $dateRegistration;

    public function __construct(string $userId, string $email)
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->dateRegistration = new \DateTime();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDateRegistration(): \DateTime
    {
        return $this->dateRegistration;
    }


}
