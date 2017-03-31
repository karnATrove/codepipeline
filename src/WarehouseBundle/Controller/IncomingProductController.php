<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Form\IncomingProductScanType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use BG\BarcodeBundle\Util\Base1DBarcode as barCode;
use BG\BarcodeBundle\Util\Base2DBarcode as matrixCode;

use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Entity\Product;

/**
 * Booking controller.
 *
 * @Route("/incoming_products")
 */
class IncomingProductController extends Controller
{
    /**
     * Lists all Incoming entity products.
     *
     * @Route("/{incoming_id}/products", name="incoming_products")
     * @Method("GET")
     */
    public function incomingProductsAction(Request $request, $incoming_id)
    {
        $em = $this->getDoctrine()->getManager();
        $incoming = $em->getRepository('WarehouseBundle:Incoming')->findOneById($incoming_id);

        $queryBuilder = $em->getRepository('WarehouseBundle:IncomingProduct')->createQueryBuilder('ip')
            ->where('ip.incoming = :incoming')
            ->setParameter('incoming',$incoming);
        
        return $this->render('incoming/products.html.twig', array(
            'incoming' => $incoming,
            'incomingProducts' => $queryBuilder->getQuery()->getResult(),
        ));
    }

    /**
     * Lists all Incoming entity products.
     *
     * @Route("/{id}/products_scanned", name="incoming_products_scanned")
     * xxxx@Method("GET")
     */
    public function incomingProductsScannedAction(Request $request, Incoming $incoming)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('WarehouseBundle:IncomingProductScan')->createQueryBuilder('ips')
            ->where('ips.incoming = :incoming')
            ->setParameter('incoming',$incoming);

        # TODO: This should go into a form type as it is duped from ScanController.php
        $form_scan = $this->createModifyScannedForm($incoming);
        $form_scan->handleRequest($request);

        # Submissions will be by ajax
        
        if ($form_scan->isSubmitted() && $form_scan->isValid()) {
            if (!$request->isXmlHttpRequest()) {
                $this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX." );
                return $this->redirect($this->generateUrl('incoming_products_scanned',array('id'=>$incoming->getId())));
            }
        }
        
        return $this->render('incoming/products_scanned.html.twig', array(
            'incoming' => $incoming,
            'incomingProducts' => $queryBuilder->getQuery()->getResult(),
            'form_scan' => $form_scan->createView(),
            'form_new' => $this->createNewScannedForm($incoming)->createView(),
        ));
    }

    /**
     * Lists all Incoming entity products.
     *
     * @Route("/{id}/ajax/products_scanned", name="incoming_products_scanned_ajax")
     */
    public function incomingProductsScannedAjaxAction(Request $request, Incoming $incoming)
    {
        $em = $this->getDoctrine()->getManager();

        $form_scan = $this->createModifyScannedForm($incoming);
        $form_scan->handleRequest($request);

        # Submissions will be by ajax
        if ($form_scan->isSubmitted() && $form_scan->isValid() && $request->isXmlHttpRequest()) {
            $response = array();

            // Ensure it isnt already completed.
            if (!$this->get('app.incoming')->isComplete($incoming)) {
                $items = $form_scan->getData();
                $em->persist($items);
                $em->flush();

                if ($form_scan->get('complete')->isClicked()) {
                    if ($this->get('app.incoming')->setComplete($incoming)) {
                        $this->get('session')->getFlashBag()->add('success', "Incoming container scanned list was saved and Incoming container is now complete." );
                    } else {
                        $this->get('session')->getFlashBag()->add('error', "An error occured while trying to set incoming to complete." );
                    }
                }

                $response['ajaxCommand'][] = array(
                    'selector' => '#scanned_form_wrap',
                    'op' => 'html',
                    'value' => $this->renderView('incoming/products_scanned_form.html.twig', array(
                        'form_scan' => $form_scan->createView(),
                        'form_new' => $this->createNewScannedForm($incoming)->createView(),
                        'incoming' => $incoming,
                    )),
                );
            } else {
                $this->get('session')->getFlashBag()->add('error', "Incoming is already set to complete." );
            }
            return new JsonResponse($response,200);
        } elseif ($form_scan->isSubmitted() && $form_scan->isValid()) {
            # Normal submission
            $this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX." );
            return $this->redirect($this->generateUrl('incoming_products_scanned',array('id'=>$incoming->getId())));
        }
    }

    /**
     * Lists all Incoming entity products.
     *
     * @Route("/{id}/ajax/products_scanned_new", name="incoming_products_scanned_new_ajax")
     */
    public function incomingProductsScannedNewAjaxAction(Request $request, Incoming $incoming)
    {
        $em = $this->getDoctrine()->getManager();

        $form_new = $this->createNewScannedForm($incoming);
        $form_new->handleRequest($request);

        # Submissions will be by ajax
        if ($form_new->isSubmitted() && $form_new->isValid() && $request->isXmlHttpRequest()) {
            $model = trim($request->request->get('form')['new']);

            # Check if it exists?
            $incomingProduct = $em->getRepository('WarehouseBundle:IncomingProduct')->findOneByModel($incoming, $model);
            $item = $em->getRepository('WarehouseBundle:IncomingProductScan')->findOneByModel($incoming,$model,FALSE); # Non assigned only
            $product = $em->getRepository('WarehouseBundle:Product')->findOneByModel($model);
                                                                                                        # 
            if (!$item) {
                # make a new scan item
                if (!$product) { # Product does not exist
                    # Create new product
                    $product = (new Product())->setUser($this->getUser())
                        ->setModel($model)
                        ->setStatus(1)
                        ->setDescription('No product description')
                        ->setQtyPerCarton(1)
                        ->setDimUnits('in')
                        ->setWeightUnits('lbs')
                        ->setCreated(new \DateTime('now'));
                    $em->persist($product);
                    $this->get('session')->getFlashBag()->add('warning', "<strong>".$model. "</strong> was created as a new product." );
                }
                $item = (new IncomingProductScan())
                    ->setIncoming($incoming)
                    ->setIncomingProduct($incomingProduct)
                    ->setQtyOnScan(1)
                    ->setProduct($product)
                    ->setCreated(new \DateTime('now'));
                
                if (!$incomingProduct) 
                    $this->get('session')->getFlashBag()->add('success', "<strong>".$model. "</strong> was not identified in the Incoming container however it was added to this list." );
                else
                    $this->get('session')->getFlashBag()->add('success', "Successfully added <strong>$model</strong>." );
            } else {
                # Update the scan item
                $item->setModified(new \DateTime('now'));
                $item->setQtyOnScan($item->getQtyOnScan() + 1);
                $this->get('session')->getFlashBag()->add('success', "Increased unassigned quantity to <strong>$model</strong>." );
            }
            $item->setUser($this->getUser());
            $em->persist($item);
            $em->flush();

            $response['ajaxCommand'][] = array(
                'selector' => '#scanned_form_wrap',
                'op' => 'html',
                'value' => $this->renderView('incoming/products_scanned_form.html.twig', array(
                    'form_scan' => $this->createModifyScannedForm($incoming)->createView(),
                    'form_new' => $form_new->createView(),
                    'incoming' => $incoming,
                )),
            );
            return new JsonResponse($response,200);

        } elseif ($form_scan->isSubmitted() && $form_scan->isValid()) {
            # Normal submission
            $this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX." );
            return $this->redirect($this->generateUrl('incoming_products_scanned',array('id'=>$incoming->getId())));
        }
    }

    /**
     * Ajax deletion of an IncomingProductScan item.
     *
     * @Route("/incoming-delete/ajax/{id}", name="incoming_products_scanned_delete_ajax")
     */
    public function incomingProductsScannedDeleteAjaxAction(Request $request, IncomingProductScan $incomingProductScan) {
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $incoming = $incomingProductScan->getIncoming();
        if ($incoming->getStatus(1,2)) { # Inbound or Arrived
            $em->remove($incomingProductScan);
            $em->flush();
        } else {
            $this->get('session')->getFlashBag()->add('error', "incoming container is no longer in active/arrived status." );
        }
        $response['ajaxCommand'][] = array(
            'selector' => '.loading',
            'op' => 'hide',
            'value' => '',
        );
        $response['ajaxCommand'][] = array(
            'selector' => '#scanned_form_wrap',
            'op' => 'html',
            'value' => $this->renderView('incoming/products_scanned_form.html.twig', array(
                'form_scan' => $this->createModifyScannedForm($incoming)->createView(),
                'form_new' => $this->createNewScannedForm($incoming)->createView(),
                'incoming' => $incoming,
            )),
        );
        return new JsonResponse($response,200);
    }

    /**
     * Creates a form to simply add IncomingProductScan to list.
     *
     * @param WarehouseBundle\Entity\Incoming $incoming The incoming
     *
     * @return SymfonyComponentFormForm The form
     */
    function createNewScannedForm(Incoming $incoming) {
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->setAction($this->generateUrl('scan_stock_ajax', array('id' => $incoming->getId())))
            ->add('new', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Enter SKU',
                    'class' => 'form-control col-xs-12',
                    'id' => 'enter-sku',
                ),
            ))
            ->add('add', SubmitType::class, array(
                'label' => 'Add',
                'attr' => array(
                    'class' => 'btn btn-success',
                )
            ))
            ->getForm();
        return $form;
    }

    /**
     * Creates a form to modify a Incoming with IncomingProductScan entity.
     *
     * @param WarehouseBundle\Entity\Incoming $incoming The incoming
     *
     * @return SymfonyComponentFormForm The form
     */
    function createModifyScannedForm(Incoming $incoming) {
        $form = $this->createFormBuilder($incoming,array(
                'csrf_protection' => false,  // <---- set this to false on a per Form Instance basis
            ))
            ->setAction($this->generateUrl('scan_stock_ajax', array('id' => $incoming->getId())))
            ->setMethod('POST')
            ->add('incomingScannedProducts',CollectionType::class,array(
                'entry_type' => IncomingProductScanType::class,
                'entry_options' => array(
                    'attr' => array('class'=>'form-control'),
                ),
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => TRUE,
            ));
        if ($incoming->getStatus() < 3) {
            $form->add('complete', SubmitType::class, array(
                'label' => 'COMPLETE SCAN',
                'attr' => array(
                    'class' => 'btn btn-dark btn-large',
                    'data-confirm' => 'This will mark the container are closed. It will also assign all items to active inventory. Are you sure you are complete?',
                )
            ));
        }
        $form = $form->getForm();
        return $form;
    }

    

    /**
     * Displays a form to create a new Incoming entity.
     *
     * @Route("/{incoming}/new", name="incoming_product_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, Incoming $incoming)
    {
    
        $incomingProduct = (new IncomingProduct())->setUser($this->getUser());
        $incomingProduct->setIncoming($incoming);
        $form   = $this->createForm('WarehouseBundle\Form\IncomingProductType', $incomingProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $incomingProduct->setCreated(new \DateTime('now'));
            $em->persist($incomingProduct);
            $em->flush();
            
            $editLink = $this->generateUrl('incoming_product_edit', array('id' => $incomingProduct->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New incoming was created successfully.</a>" );
            
            if ($request->get('submit') == 'save')
                return $this->redirectToRoute('incoming_products',array('incoming_id'=>$incoming->getId()));
            else
            return $this->redirectToRoute('incoming_product_new',array('incoming'=>$incoming->getId()));
        }
        return $this->render('incoming/product/new.html.twig', array(
            'incoming' => $incoming,
            'form'   => $form->createView(),
        ));
    }
    

    /**
     * Displays a form to edit an existing Incoming entity.
     *
     * @Route("/{id}/edit", name="incoming_product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, IncomingProduct $incomingProduct)
    {
        $deleteForm = $this->createDeleteForm($incomingProduct);
        $editForm = $this->createForm('WarehouseBundle\Form\IncomingProductType', $incomingProduct);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($incomingProduct);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('incoming_product_edit', array('id' => $incomingProduct->getId()));
        }
        return $this->render('incoming/product/edit.html.twig', array(
            'incomingProduct' => $incomingProduct,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Deletes a Incoming entity.
     *
     * @Route("/{incoming}/{id}", name="incoming_product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Incoming $incoming, IncomingProduct $incomingProduct)
    {
    
        $form = $this->createDeleteForm($incomingProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($incomingProduct);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Incoming Product was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Incoming Product');
        }
        
        return $this->redirectToRoute('incoming_products',array('incoming_id'=>$incoming->getId()));
    }
    
    /**
     * Creates a form to delete a Incoming entity.
     *
     * @param Incoming $incoming The Incoming entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(IncomingProduct $incomingProduct)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('incoming_product_delete', array('incoming'=>$incomingProduct->getIncoming()->getId(),'id' => $incomingProduct->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Delete Incoming by id
     *
     * @Route("/{incoming}/delete/{id}", name="incoming_product_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(Incoming $incoming, IncomingProduct $incomingProduct){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->remove($incomingProduct);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Incoming Product was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Incoming Product');
        }

        return $this->redirect($this->generateUrl('incoming_products',array('incoming_id'=>$incoming->getId())));

    }
    

    /**
    * Bulk Action
    * @Route("/{incoming}/bulk-action", name="incoming_product_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request, Incoming $incoming)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        $cnt_changes = 0;
        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('WarehouseBundle:IncomingProduct');

                foreach ($ids as $id) {
                    $incomingProduct = $repository->find($id);
                    # Remove incoming products before being able to delete incoming container

                    $em->remove($incomingProduct);
                    $em->flush();
                    $cnt_changes++;
                }

                $this->get('session')->getFlashBag()->add('success', $cnt_changes. ' incoming products were deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the incoming products ');
            }
        }

        return $this->redirect($this->generateUrl('incoming_products',array('incoming_id'=>$incoming->getId())));
    }

}
