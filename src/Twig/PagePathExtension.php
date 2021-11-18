<?php

namespace TwinElements\PageBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PagePathExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
          new TwigFunction('pagePath', [PagePathRuntime::class, 'generatePath'])
        ];
    }
}
