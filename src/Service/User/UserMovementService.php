<?php

namespace App\Service\User;

use App\Entity\Project\Project;
use App\Entity\User\User;
use App\Service\Helper\DefaultNormalizer;
use App\Service\Integration\MercureIntegration;

class UserMovementService
{
    public function __construct(
        private MercureIntegration $mercureIntegration,
        private DefaultNormalizer $defaultNormalizer,
    ) { }

    public function registerMouseMovement(User $user, Project $project, string $routeName, float $mouseRelativeX, float $mouseRelativeY, ?string $hoveredElementDomPath, ?float $hoveredElementOffsetRelativeX, ?float $hoveredElementOffsetRelativeY): void
    {
        $this->mercureIntegration->publish(
            $project,
            'UserMovement',
            [
                'user' => $this->defaultNormalizer->normalize($user, $user, maxDepth: 2),
                'routeName' => $routeName,
                'relativeX' => $mouseRelativeX,
                'relativeY' => $mouseRelativeY,
                'hoveredElementDomPath' => $hoveredElementDomPath,
                'hoveredElementOffsetRelativeX' => $hoveredElementOffsetRelativeX,
                'hoveredElementOffsetRelativeY' => $hoveredElementOffsetRelativeY,
            ],
            user: $user,
        );
    }
}