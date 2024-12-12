<?php

namespace App\Entity\Interface;

interface CrudEntityInterface
{
    public function initialize(): static;
}