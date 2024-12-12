<?php

namespace App\Entity\Interface;

use App\Entity\User;

interface UserPermissionInterface
{
    public function getId(): ?int;
    public function hasUserAccess(User $user): bool;
}