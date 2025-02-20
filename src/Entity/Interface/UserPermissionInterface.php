<?php

namespace App\Entity\Interface;

use App\Entity\User\User;

/**
 * Base interface for all entities which require user authorization.
 * It is important to differentiate between user AUTHENTICATION and AUTHORIZATION.
 * In the context of the UserPermissionInterface, AUTHORIZATION is the key. The user is already authenticated as the currently logged in user.
 * The UserPermissionInterface is used to check if the currently logged in user has access to a specific entity, this can vary dependending on the user and the access context.
 * Possible access contexts are read, update, delete, download etc. (check out App\Entity\Interface\AccessContext for all possible values)
 */
interface UserPermissionInterface
{
    public function getId(): ?int;
    public function hasUserAccess(User $user, AccessContext $accessContext = AccessContext::READ): bool;
}