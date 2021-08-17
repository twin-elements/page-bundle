<?php

namespace TwinElements\PageBundle;

use TwinElements\AdminBundle\Menu\AdminMenuInterface;
use TwinElements\AdminBundle\Menu\MenuItem;

class AdminMenu implements AdminMenuInterface
{
    public function getItems()
    {
        return [
            MenuItem::newInstance('page.pages', 'page_index', [], 5),
        ];
    }
}
