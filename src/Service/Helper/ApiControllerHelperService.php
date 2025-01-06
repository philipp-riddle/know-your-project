<?php

namespace App\Service\Helper;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This service is used as a base for all API controllers to provide common functionality.
 * Through bundling all the services all API controllers need through one service we avoid rewriting the constructors in every controller whenever the base class changes.
 */
class ApiControllerHelperService
{
    public function __construct(
        public EntityManagerInterface $em,
        public SerializerInterface $serializer,
        public UserRepository $userRepository,
    ) { }
}