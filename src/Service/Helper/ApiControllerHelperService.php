<?php

namespace App\Service\Helper;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This service is used as a base for all API controllers to provide common functionality.
 * Through bundling all the services all API controllers need through one service we avoid rewriting the constructors in every controller whenever the base class changes.
 */
final class ApiControllerHelperService
{
    public function __construct(
        public readonly EntityManagerInterface $em,
        public readonly UserRepository $userRepository,
        public readonly EventDispatcherInterface $eventDispatcher,
        public readonly DefaultNormalizer $defaultNormalizer,
    ) { }
}