<?php

namespace WarehouseBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\ProductLog;

/**
 * Product controller.
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @Route("/", name="product")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('WarehouseBundle:Product')->createQueryBuilder('e');

        // Remove deleted
        if (empty($request->get('status')) && !(is_numeric($request->get('status')) && intval($request->get('status')) == 0)) {
            $queryBuilder->andWhere('e.status <> :pstatus');
            $queryBuilder->setParameter('pstatus',0);
        }

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($products, $pagerHtml) = $this->paginator($queryBuilder, $request);

        return $this->render('product/index.html.twig', array(
            'products' => $products,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),

        ));
    }


    /**
     * Create filter form and process filter request.
     *
     */
    protected function filter(QueryBuilder $queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm('WarehouseBundle\Form\ProductFilterType');

        # Default sort
        if (empty($request->request->set('pcg_sort_col',''))) {
            $request->request->set('pcg_sort_col','model');
            $request->request->set('pcg_sort_order','asc');
        }

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('ProductControllerFilter');
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
                $session->set('ProductControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ProductControllerFilter')) {
                $filterData = $session->get('ProductControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('WarehouseBundle\Form\ProductFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);


    }

    /**
     * Get results from paginator and get paginator view.
     *
     */
    protected function paginator(QueryBuilder $queryBuilder, Request $request)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias().'.'.$request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show' , 50));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }

        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request) {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('product', $requestParams);
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
     * Displays a form to create a new Product entity.
     *
     * @Route("/new", name="product_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {

        $product = (new Product())->setUser($this->getUser());
        $product->setStatus(1); # Default to active when creating
        $product->setCreated(new \DateTime('now')); # Default created date
        $form   = $this->createForm('WarehouseBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            # Additional validation
            $existing = $em->getRepository('WarehouseBundle:Product')->findOneByModel($product->getModel());
            if (!$existing) {
                $em->persist($product);
                $em->flush();

                $editLink = $this->generateUrl('product_edit', array('id' => $product->getId()));
                $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New product was created successfully.</a>" );

                $nextAction=  $request->get('submit') == 'save' ? 'product' : 'product_new';
                return $this->redirectToRoute($nextAction);
            } else {
                $this->get('session')->getFlashBag()->add('error', 'Product model already exists '. $existing->getId());
            }
        }
        return $this->render('product/new.html.twig', array(
            'product' => $product,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Product $product)
    {
        //$original_product = clone $product;
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($product);
        $locationProductForm = $this->createLocationProductForm($product);
        $editForm = $this->createForm('WarehouseBundle\Form\ProductType', $product);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            # Log the save
            $log = new ProductLog();
            $log->setUser($this->getUser())
                ->setProduct($product)
                ->setCreated(new \DateTime('now'))
                ->setNote('Changes were saved from the product edit page.');

            $em->persist($product);
            $em->persist($log);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('product_edit', array('id' => $product->getId()));
        } elseif ($editForm->isSubmitted()) {
            foreach($editForm->getErrors(true, false) as $error) {
                $this->get('session')->getFlashBag()->add('error', $error );
            }
        }
        return $this->render('product/edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'location_form' => $locationProductForm->createView(),
            'log' => $em->getRepository('WarehouseBundle:ProductLog')->getLogByProduct($product,10)
        ));
    }


    /**
     * Deletes a Product entity.
     *
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //$product = $em->getRepository('WarehouseBundle:Product')->find($product->getId());
            if (!$product) {
                throw $this->createNotFoundException(
                    'No product found for id '. $productId
                );
            }

            $error = FALSE;
            # Remove all locations first
            if (count($product->getLocations())) {
                $this->get('session')->getFlashBag()->add('error', $product->getModel().' has active inventory locations. Please remove before deleting product.');
                /* 
                foreach($product->getLocations() as $location) {
                    $em->remove($location);
                }
                */
                $error = TRUE;
            }
            if (count($product->getIncoming())) {
                $this->get('session')->getFlashBag()->add('error', $product->getModel().' has incoming inventory. We can not remove product at this time.');
                /* 
                foreach($product->getIncoming() as $incoming) {
                    $em->remove($incoming);
                }
                */
                $error = TRUE;
            }

            if ($em->getRepository('WarehouseBundle:BookingProduct')->getAllocatedQuantityByProduct($product)) {
                $this->get('session')->getFlashBag()->add('error', $product->getModel().' has allocated inventory. We can not remove product at this time.');
                /* 
                foreach($product->getIncoming() as $incoming) {
                    $em->remove($incoming);
                }
                */
                $error = TRUE;
            }

            if ($error === FALSE) {
                $em->remove($product);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', $product->getModel().' was deleted successfully');
            }
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Product');
        }

        return $this->redirectToRoute('product', $request->query->all());
    }

    /**
     * Creates a form to delete a Product entity.
     *
     * @param Product $product The Product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Delete Product by id
     *
     * @Route("/delete/{id}", name="product_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(Product $product){
        $em = $this->getDoctrine()->getManager();

        try {
            $error = FALSE;
            # Remove all locations first
            if (count($product->getLocations())) {
                $this->get('session')->getFlashBag()->add('error', $product->getModel().' has active inventory locations. Please remove before deleting product.');
                /* 
                foreach($product->getLocations() as $location) {
                    $em->remove($location);
                }
                */
                $error = TRUE;
            }
            if (count($product->getIncoming())) {
                $this->get('session')->getFlashBag()->add('error', $product->getModel().' has incoming inventory. We can not remove product at this time.');
                /* 
                foreach($product->getIncoming() as $incoming) {
                    $em->remove($incoming);
                }
                */
                $error = TRUE;
            }
            if ($em->getRepository('WarehouseBundle:BookingProduct')->getAllocatedQuantityByProduct($product)) {
                $this->get('session')->getFlashBag()->add('error', $product->getModel().' has allocated inventory. We can not remove product at this time.');
                /* 
                foreach($product->getIncoming() as $incoming) {
                    $em->remove($incoming);
                }
                */
                $error = TRUE;
            }

            if ($error === FALSE) {
                $em->remove($product);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'The Product was deleted successfully');
            }
        } catch (\Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Product');
        }

        return $this->redirect($this->generateUrl('product'));

    }


    /**
     * Bulk Action
     * @Route("/bulk-action/", name="product_bulk_action")
     * @Method("POST")
     */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('WarehouseBundle:Product');

                $count = 0;
                foreach ($ids as $id) {
                    $product = $repository->find($id);
                    $error = FALSE;
                    # Remove all locations first
                    if (count($product->getLocations())) {
                        $this->get('session')->getFlashBag()->add('error', $product->getModel().'  has active inventory locations. Please remove before deleting product.');
                        /* 
                        foreach($product->getLocations() as $location) {
                            $em->remove($location);
                        }
                        */
                        $error = TRUE;
                    }
                    if (count($product->getIncoming())) {
                        $this->get('session')->getFlashBag()->add('error', $product->getModel().' has incoming inventory. We can not remove product at this time.');
                        /* 
                        foreach($product->getIncoming() as $incoming) {
                            $em->remove($incoming);
                        }
                        */
                        $error = TRUE;
                    }
                    if ($em->getRepository('WarehouseBundle:BookingProduct')->getAllocatedQuantityByProduct($product)) {
                        $this->get('session')->getFlashBag()->add('error', $product->getModel().' has allocated inventory. We can not remove product at this time.');
                        /* 
                        foreach($product->getIncoming() as $incoming) {
                            $em->remove($incoming);
                        }
                        */
                        $error = TRUE;
                    }

                    if ($error === FALSE) {
                        $em->remove($product);
                        $em->flush();
                        $count++;
                    }
                }
                if ($count)
                    $this->get('session')->getFlashBag()->add('success', $count. ' product(s) were deleted successfully!');

            } catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the products ');
            }
        }

        return $this->redirect($this->generateUrl('product', $request->query->all()));
    }


    /**
     * Creates a form to add Location to a Product entity.
     *
     * @param Product $product The Product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createLocationProductForm(Product $product)
    {
        $locationProduct = new \WarehouseBundle\Entity\LocationProduct();
        $locationProduct->setProduct($product);

        $form = $this->createForm('WarehouseBundle\Form\LocationProductType', $locationProduct,
            array(
                'action' => $this->generateUrl('location_product_new_ajax',array('product_id'=>$product->getId())),
                'method' => 'POST',
            )
        );

        return $form;
    }

}
