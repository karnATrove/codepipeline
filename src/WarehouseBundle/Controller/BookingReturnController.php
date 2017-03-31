<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\BookingReturn;

/**
 * BookingReturn controller.
 *
 * @Route("/return")
 */
class BookingReturnController extends Controller
{
    /**
     * Lists all BookingReturn entities.
     *
     * @Route("/", name="return")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('WarehouseBundle:BookingReturn')->createQueryBuilder('e');
        
        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($bookingReturns, $pagerHtml) = $this->paginator($queryBuilder, $request);
        
        return $this->render('bookingreturn/index.html.twig', array(
            'bookingReturns' => $bookingReturns,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),

        ));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter($queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm('WarehouseBundle\Form\BookingReturnFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('BookingReturnControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->handleRequest($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('BookingReturnControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('BookingReturnControllerFilter')) {
                $filterData = $session->get('BookingReturnControllerFilter');
                
                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }
                
                $filterForm = $this->createForm('WarehouseBundle\Form\BookingReturnFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }


    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder, Request $request)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias().'.'.$request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show' , 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }
        
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request)
        {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('return', $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => 'previous',
            'next_message' => 'next',
        ));

        return array($entities, $pagerHtml);
    }
    
    

    /**
     * Displays a form to create a new BookingReturn entity.
     *
     * @Route("/new", name="return_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
    
        $bookingReturn = new BookingReturn();
        $form   = $this->createForm('WarehouseBundle\Form\BookingReturnType', $bookingReturn);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($bookingReturn);
            $em->flush();
            
            $editLink = $this->generateUrl('return_edit', array('id' => $bookingReturn->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New bookingReturn was created successfully.</a>" );
            
            $nextAction=  $request->get('submit') == 'save' ? 'return' : 'return_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('bookingreturn/new.html.twig', array(
            'bookingReturn' => $bookingReturn,
            'form'   => $form->createView(),
        ));
    }
    

    /**
     * Finds and displays a BookingReturn entity.
     *
     * @Route("/{id}", name="return_show")
     * @Method("GET")
     */
    public function showAction(BookingReturn $bookingReturn)
    {
        $deleteForm = $this->createDeleteForm($bookingReturn);
        return $this->render('bookingreturn/show.html.twig', array(
            'bookingReturn' => $bookingReturn,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Displays a form to edit an existing BookingReturn entity.
     *
     * @Route("/{id}/edit", name="return_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, BookingReturn $bookingReturn)
    {
        $deleteForm = $this->createDeleteForm($bookingReturn);
        $editForm = $this->createForm('WarehouseBundle\Form\BookingReturnType', $bookingReturn);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($bookingReturn);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('return_edit', array('id' => $bookingReturn->getId()));
        }
        return $this->render('bookingreturn/edit.html.twig', array(
            'bookingReturn' => $bookingReturn,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Deletes a BookingReturn entity.
     *
     * @Route("/{id}", name="return_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, BookingReturn $bookingReturn)
    {
    
        $form = $this->createDeleteForm($bookingReturn);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($bookingReturn);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The BookingReturn was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the BookingReturn');
        }
        
        return $this->redirectToRoute('return');
    }
    
    /**
     * Creates a form to delete a BookingReturn entity.
     *
     * @param BookingReturn $bookingReturn The BookingReturn entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(BookingReturn $bookingReturn)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('return_delete', array('id' => $bookingReturn->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Delete BookingReturn by id
     *
     * @Route("/delete/{id}", name="return_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(BookingReturn $bookingReturn){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->remove($bookingReturn);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The BookingReturn was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the BookingReturn');
        }

        return $this->redirect($this->generateUrl('return'));

    }
    

    /**
    * Bulk Action
    * @Route("/bulk-action/", name="return_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('WarehouseBundle:BookingReturn');

                foreach ($ids as $id) {
                    $bookingReturn = $repository->find($id);
                    $em->remove($bookingReturn);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'bookingReturns was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the bookingReturns ');
            }
        }

        return $this->redirect($this->generateUrl('return'));
    }
    

}
