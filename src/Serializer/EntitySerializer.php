<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

class EntitySerializer
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serializa una entidad con los grupos indicados.
     *
     * @param object $entity La entidad a serializar
     * @param array $groups Grupos de serialización
     * @return string El resultado en formato JSON
     */
    public function serializeToJson(object $entity, array $groups): string
    {
        return $this->serializer->serialize($entity, 'json', ['groups' => $groups]);
    }
}
