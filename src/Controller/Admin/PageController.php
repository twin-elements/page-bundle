<?php

namespace TwinElements\PageBundle\Controller\Admin;

use TwinElements\AdminBundle\Model\CrudControllerTrait;
use TwinElements\PageBundle\Entity\Page\Page;
use TwinElements\PageBundle\Form\PageType;
use TwinElements\PageBundle\Repository\PageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use TwinElements\AdminBundle\Role\AdminUserRole;


/**
 * @Route("page")
 */
class PageController extends AbstractController
{

    use CrudControllerTrait;

    /**
     * @Route("/", name="page_index", methods={"GET"})
     */
    public function indexAction(Request $request, PaginatorInterface $paginator, PageRepository $pageRepository, TranslatorInterface $translator)
    {
        try {
            $limit = 20;
            if ($request->query->has('limit')) {
                $limit = $request->query->getInt('limit');
            }
            $pagesQuery = $pageRepository->findIndexListItemsQuery($request->getLocale());

            $pages = $paginator->paginate(
                $pagesQuery,
                $request->query->getInt('page', 1),
                $limit
            );

            $this->breadcrumbs->setItems([
                $translator->trans('page.pages_list', [], 'messages', $this->adminLocale) => null
            ]);

            return $this->render('@TwinElementsPage/index.html.twig', array(
                'pages' => $pages,
            ));
        } catch (\Exception $exception) {
            $this->flashes->errorMessage($exception->getMessage());

            return $this->redirectToRoute('admin_dashboard');
        }

    }

    /**
     * @Route("/new", name="page_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        try {
            $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);

            $page = new Page();
            $page->setCurrentLocale($request->getLocale());
            $form = $this->createForm(PageType::class, $page);
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
                $translator->trans('page.pages_list', [], 'messages', $this->adminLocale) => $this->generateUrl('page_index'),
                $translator->trans('page.adding_a_new_page', [], 'messages', $this->adminLocale) => null
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
    public function editAction(int $id, Request $request, PageRepository $pageRepository, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_USER);

        /**
         * @var Page $page
         */
        $page = $pageRepository->find($id);

        $deleteForm = $this->createDeleteForm($page);
        $editForm = $this->createForm(PageType::class, $page, [
            'is_content' => (is_null($page->getIsContentFor()) ? false : true)
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

        $this->breadcrumbs->setItems([
            $translator->trans('page.pages_list', [], 'messages', $this->adminLocale) => $this->generateUrl('page_index'),
            $page->getTitle() => null
        ]);

        return $this->render('@TwinElementsPage/edit.html.twig', array(
            'entity' => $page,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * @Route("/{id}/add-new-content", name="page_add_new_content", methods={"POST", "GET"})
     */
    public function addContent(int $id, Request $request, PageRepository $repository)
    {
        $parent = $repository->find($id);
        $page = new Page();
        $page->setIsContentFor($parent);
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
    public function deleteAction(Request $request, Page $page, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(AdminUserRole::ROLE_ADMIN);

        $form = $this->createDeleteForm($page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($page->getRoute() != '') {
                $this->flashes->errorMessage($translator->trans('page.this_page_has_a_module', [], 'messages', $this->adminLocale));
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
