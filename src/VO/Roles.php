<?php

declare(strict_types=1);

namespace App\VO;

final class Roles
{
    private array $roles;

    public function __construct(array $roles = [])
    {
        $this->roles = $this->validateRoles($roles);
    }

    private function validateRoles(array $roles): array
    {
        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }

        foreach ($roles as $role) {
            if (!str_starts_with($role, 'ROLE_')) {
                throw new \InvalidArgumentException("El rol '{$role}' no es válido. Todos los roles deben comenzar con 'ROLE_'");
            }
        }

        return array_unique($roles);
    }

    public function getValue(): array
    {
        return $this->roles;
    }

    public function __toString(): string
    {
        return implode(',', $this->roles);
    }
}
