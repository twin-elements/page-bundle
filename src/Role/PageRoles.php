<?php

namespace TwinElements\PageBundle\Role;

use TwinElements\AdminBundle\Role\RoleGroupInterface;

final class PageRoles implements RoleGroupInterface
{
    const ROLE_PAGE_FULL = 'ROLE_PAGE_FULL';
    const ROLE_PAGE_EDIT = 'ROLE_PAGE_EDIT';
    const ROLE_PAGE_VIEW = 'ROLE_PAGE_VIEW';

    public static function getRoles(): array
    {
        return [self::ROLE_PAGE_FULL, self::ROLE_PAGE_EDIT, self::ROLE_PAGE_VIEW];
    }
}
