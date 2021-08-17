<?php

namespace TwinElements\PageBundle;

use TwinElements\PageBundle\Entity\Page\Page;
use TwinElements\PageBundle\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Exception;
use TwinElements\FormExtensions\Component\UrlBuilder\ModuleUrlGeneratorInterface;

class PageUrlGenerator implements ModuleUrlGeneratorInterface
{
    /**
     * @var RouterInterface $router
     */
    private $router;
    /**
     * @var PageRepository $pageRepository
     */
    private $pageRepository;

    /**
     * @var Request $request
     */
    private $request;

    public static function getName(): string
    {
        return 'page';
    }

    public function __construct(RouterInterface $router, PageRepository $pageRepository, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->pageRepository = $pageRepository;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function generateUrl(int $id)
    {
        /**
         * @var Page $page
         */
        $page = $this->pageRepository->find($id);

        if (!is_null($page->getRoute())) {
            return $this->router->generate($page->getRoute());
        }

        return $this->router->generate('front_page', [
            'id' => $page->getId(),
            'slug' => $page->getSlug()
        ]);
    }

    public function getUrlList()
    {
        return $this->pageRepository->findIndexListItems($this->request->getLocale());
    }
}
