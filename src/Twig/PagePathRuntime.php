<?php

namespace TwinElements\PageBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use TwinElements\PageBundle\PagePath;

class PagePathRuntime implements RuntimeExtensionInterface
{
    /**
     * @var PagePath $pagePath
     */
    private $pagePath;

    public function __construct(PagePath $pagePath)
    {
        $this->pagePath = $pagePath;
    }

    public function generatePath(int $id, string $slug)
    {
        return $this->pagePath->generatePath($id, $slug);
    }
}
