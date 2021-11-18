<?php

namespace TwinElements\PageBundle;

use Symfony\Component\Routing\RouterInterface;

final class PagePath
{
    const ROUTE = 'front_page';

    /**
     * @var RouterInterface $router
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function generatePath(int $id, string $slug)
    {
        return $this->router->generate(self::ROUTE, [
            'id' => $id,
            'slug' => $slug
        ]);
    }
}
