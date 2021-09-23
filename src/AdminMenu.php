<?php

namespace TwinElements\PageBundle;

use TwinElements\AdminBundle\Menu\AdminMenuInterface;
use TwinElements\AdminBundle\Menu\MenuItem;
use TwinElements\PageBundle\Entity\Page\Page;
use TwinElements\PageBundle\Security\PageVoter;

class AdminMenu implements AdminMenuInterface
{
    public function getItems()
    {
        return [
            MenuItem::newInstance('page.pages', 'page_index', [], 2, null, [PageVoter::VIEW, new Page()]),
        ];
    }
}
