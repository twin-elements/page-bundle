<?php

namespace TwinElements\PageBundle\Controller\Admin;

use TwinElements\AdminBundle\Entity\Traits\PositionInterface;
use TwinElements\AdminBundle\Model\CrudControllerTrait;
use TwinElements\PageBundle\Entity\Page\Page;
use TwinElements\PageBundle\Entity\SearchPage;
use TwinElements\PageBundle\Form\PageType;
use TwinElements\PageBundle\Form\SearchPageType;
use TwinElements\PageBundle\Repository\PageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use TwinElements\PageBundle\Security\PageVoter;
use function Doctrine\ORM\QueryBuilder;


/**
 * @Route("page")
 */
class PageController extends AbstractController
{

    use CrudControllerTrait;

    /**
     * @Route("/", name="page_index", methods={"GET","POST"})
     */
    public function index(Request $request, PaginatorInterface $paginator, PageRepository $pageRepository)
    {
        try {
            $entity = new Page();
            $this->denyAccessUnlessGranted(PageVoter::VIEW, $entity);

            $limit = 20;
            if ($request->query->has('limit')) {
                $limit = $request->query->getInt('limit');
            }
            $search = new SearchPage();
            $searchForm = $this->createForm(SearchPageType::class, $search, [
                'action' => $this->generateUrl('page_search'),
                'method' => 'GET'
            ]);
            $searchForm->handleRequest($request);

            $pagesQB = $pageRepository->findIndexListItemsQB($request->getLocale());

            $pages = $paginator->paginate(
                $pagesQB->getQuery(),
                $request->query->getInt('page', 1),
                $limit
            );

            $this->breadcrumbs->setItems([
                $this->adminTranslator->translate('page.pages_list') => null
            ]);

            $responseParameters = [
                'pages' => $pages,
                'limit' => $limit,
                'searchForm' => $searchForm->createView()
            ];

            if ((new \ReflectionClass(Page::class))->implementsInterface(PositionInterface::class)) {
                $responseParameters['sortable'] = Page::class;
            }

            return $this->render('@TwinElementsPage/index.html.twig', $responseParameters);
        } catch (\Exception $exception) {
            $this->flashes->errorMessage($exception->getMessage());

            return $this->redirectToRoute('admin_dashboard');
        }

    }

    /**
     * @Route("/search", name="page_search", methods={"GET"})
     */
    public function search(
        Request        $request,
        PageRepository $pageRepository
    )
    {
        try {
            $this->denyAccessUnlessGranted(PageVoter::VIEW, new Page());

            $search = new SearchPage();
            $searchForm = $this->createForm(SearchPageType::class, $search, [
                'action' => $this->generateUrl('page_search'),
                'method' => 'GET'
            ]);
            $searchForm->handleRequest($request);
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                if (is_null($search->getTitle())) {
                    throw new \Exception('No query');
                }
                $pages = $pageRepository->search($request->getLocale(), $search->getTitle());

                $this->breadcrumbs->getBreadcrumbs()
                    ->addItem($this->adminTranslator->translate('page.pages_list'), $this->generateUrl('page_index'))
                    ->addItem($this->adminTranslator->translate('page.search_results'));

                return $this->render('@TwinElementsPage/search.html.twig', [
                    'pages' => $pages,
                    'searchForm' => $searchForm->createView(),
                    'searchTitle' => $search->getTitle()
                ]);
            }

            return $this->generateUrl('page_index', $request->query->all());

        } catch (\Exception $exception) {
            $this->flashes->errorMessage($exception->getMessage());

            return $this->redirectToRoute('page_index');
        }
    }

    /**
     * @Route("/new", name="page_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        try {
            $this->denyAccessUnlessGranted(PageVoter::FULL, new Page());

            $page = new Page();
            if ($request->query->has('is_separate_content')) {
                $page->setIsSeparateContent(true);
            }
            $page->setCurrentLocale($request->getLocale());
            $form = $this->createForm(PageType::class, $page, [
                'is_content' => ($page->isSeparateContent() ? true : false)
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                $em->persist($page);
                $page->mergeNewTranslations();
                $em->flush();

                $this->crudLogger->createLog($page->getId(), $page->getTitle());

                $this->flashes->successMessage();

                if ('save' === $form->getClickedButton()->getName()) {
                    return $this->redirectToRoute('page_edit', array('id' => $page->getId()));
                } else {
                    return $this->redirectToRoute('page_index');
                }

            }

            $this->breadcrumbs->setItems([
                $this->adminTranslator->translate('page.pages_list') => $this->generateUrl('page_index'),
                $this->adminTranslator->translate('page.adding_a_new_page') => null
            ]);

            return $this->render('@TwinElementsPage/new.html.twig', array(
                'page' => $page,
                'form' => $form->createView(),
            ));
        } catch (\Exception $exception) {
            $this->flashes->errorMessage($exception->getMessage());

            return $this->redirectToRoute('page_index');
        }
    }

    /**
     * @Route("/{id}/edit", name="page_edit", methods={"GET", "POST"})
     */
    public function editAction(int $id, Request $request, PageRepository $pageRepository)
    {
        $this->denyAccessUnlessGranted(PageVoter::EDIT, new Page());

        /**
         * @var Page $page
         */
        $page = $pageRepository->find($id);

        $deleteForm = $this->createDeleteForm($page);
        $editForm = $this->createForm(PageType::class, $page, [
            'is_content' => ((!is_null($page->getIsContentFor()) || $page->isSeparateContent()) ? true : false)
        ]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $page->mergeNewTranslations();

                $em->flush();
                $this->crudLogger->createLog($page->getId(), $page->getTitle());

                $this->flashes->successMessage();
            } catch (\Exception $exception) {
                $this->flashes->errorMessage($exception->getMessage());
            }

            if ('save' === $editForm->getClickedButton()->getName()) {
                return $this->redirectToRoute('page_edit', array('id' => $page->getId()));
            } else {
                return $this->redirectToRoute('page_index');
            }
        }

        $breadcrumbs = $this->breadcrumbs->getBreadcrumbs();
        $breadcrumbs->addItem($this->adminTranslator->translate('page.pages_list'), $this->generateUrl('page_index'));
        if (!is_null($page->getIsContentFor())) {
            $breadcrumbs->addItem($page->getIsContentFor()->getTitle(), $this->generateUrl('page_edit', [
                'id' => $page->getIsContentFor()->getId()
            ]));
        }
        $breadcrumbs->addItem($page->getTitle(), null);

        $responseParameters = [
            'entity' => $page,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ];

        if ($page->getChildrenContents()->count() > 0) {
            $responseParameters['sortable'] = Page::class;
        }

        return $this->render('@TwinElementsPage/edit.html.twig', $responseParameters);
    }

    /**
     * @Route("/{id}/add-new-content", name="page_add_new_content", methods={"POST", "GET"})
     */
    public function addContent(int $id, Request $request, PageRepository $repository)
    {
        $this->denyAccessUnlessGranted(PageVoter::FULL, new Page());
        $parent = $repository->find($id);
        $page = new Page();
        $page->setIsContentFor($parent);
        if ($parent->isSeparateContent()) {
            $page->setIsSeparateContent(true);
        }
        $page->setCurrentLocale($request->getLocale());
        $form = $this->createForm(PageType::class, $page, [
            'is_content' => true
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($page);
                $page->mergeNewTranslations();

                $em->flush();

                $this->crudLogger->createLog($page->getId(), $page->getTitle());
                $this->flashes->successMessage();

                if ('save' === $form->getClickedButton()->getName()) {
                    return $this->redirectToRoute('page_edit', array('id' => $page->getId()));
                } else {
                    return $this->redirectToRoute('page_index');
                }
            } catch (\Exception $exception) {
                $this->flashes->errorMessage($exception->getMessage());
                return $this->redirectToRoute('page_index');
            }
        }

        return $this->render('@TwinElementsPage/new_content.html.twig', [
            'entity' => $page,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="page_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Page $page)
    {
        $this->denyAccessUnlessGranted(PageVoter::FULL, new Page());

        $form = $this->createDeleteForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($page->getRoute() != '') {
                $this->flashes->errorMessage($this->adminTranslator->translate('page.this_page_has_a_module'));
                return $this->redirectToRoute('page_edit', [
                    'id' => $page->getId()
                ]);
            }

            try {
                $id = $page->getId();
                $title = $page->getTitle();

                $em = $this->getDoctrine()->getManager();
                $em->remove($page);
                $em->flush();

                $this->crudLogger->createLog($id, $title);

                $this->flashes->successMessage();
            } catch (\Exception $exception) {
                $this->flashes->errorMessage($exception->getMessage());
            }

        }

        return $this->redirectToRoute('page_index');
    }

    /**
     * Creates a form to delete a page entity.
     *
     * @param Page $page The page entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Page $page)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('page_delete', array('id' => $page->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
