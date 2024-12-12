<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Interface\UserPermissionInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class ManyToManyEntityHandler
{
    public function handle(User $user, array|Collection $original, array|Collection $new, callable $add, callable $remove): void
    {
        $original = $original instanceof Collection ? \iterator_to_array($original) : $original;
        $new = $new instanceof Collection ? \iterator_to_array($new) : $new;

        foreach ($original as $entity) {
            if (!\in_array($entity, $new, true)) {
                $remove($entity);
            }
        }

        foreach ($new as $entity) {
            if (!\in_array($entity, $original, true)) {
                if ($entity instanceof UserPermissionInterface && !$entity->hasUserAccess($user)) {
                    throw new AccessDeniedException(\sprintf('You do not have access to organization role "%s"', $entity::class));
                }

                $add($entity);
            }
        }
    }
}