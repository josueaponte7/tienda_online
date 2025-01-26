<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepositoryInterface;
use App\VO\Email;
use App\VO\Password;

class UserService
{


    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(RegisterUserDTO $dto): User
    {

        $email = new Email($dto->email);

        if ($this->userRepository->existsByEmail($email->getValue())) {
            throw new \Exception('User already exists.');
        }

        $password = new Password($dto->password);
        $user = UserFactory::create($email, $password);
        $this->userRepository->save($user);
        return $this->userRepository->findByEmail($dto->email);
    }
}
