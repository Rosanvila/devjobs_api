<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class RequiresAuth
{
    public function __construct(
        public array $roles = ['ROLE_USER']
    ) {}
}
