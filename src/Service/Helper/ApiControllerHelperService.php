<?php

namespace App\Service\Helper;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiControllerHelperService
{
    public function __construct(
        public EntityManagerInterface $em,
        public SerializerInterface $serializer,
        public UserRepository $userRepository,
    ) { }
}