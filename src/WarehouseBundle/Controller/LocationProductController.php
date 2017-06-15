<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\ProductLog;
use WarehouseBundle\Form\LocationProductType;


#http://codemonkeys.be/2013/01/ajaxify-your-symfony2-forms-with-jquery/

/**
 * Booking controller.
 *
 * @Route("/location_product")
 */
class LocationProductController extends Controller
{

    /**
     * Displays a form to edit an existing Location entity.
     *
     * @Route("/{id}/edit", name="location_product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, LocationProduct $locationProduct)
    {
        $original_locationProduct = clone $locationProduct; # Store for logging
        $deleteForm = $this->createDeleteForm($locationProduct);
        $editForm = $this->createForm('WarehouseBundle\Form\LocationProductType', $locationProduct);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            # Log the save
            $log = (new ProductLog())
                ->setUser($this->getUser())
                ->setProduct($locationProduct->getProduct())
                ->setCreated(new \DateTime('now'))
                ->setNote('Quantity edited from ' . $original_locationProduct->getOnHand(). ' to '.$locationProduct->getOnHand(). ' at location '. $locationProduct->getLocation()->getAisle().'-'.$locationProduct->getLocation()->getRow().'-'.$locationProduct->getLocation()->getLevel().'.');

            $em->persist($log);    
            $em->persist($locationProduct);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('location_product_edit', array('id' => $locationProduct->getId()));
        }
        return $this->render('location_product/edit.html.twig', array(
            'locationProduct' => $locationProduct,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes an LocationProduct entity.
     *
     * @Route("/{id}", name="location_product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, LocationProduct $locationProduct)
    {
    
        $form = $this->createDeleteForm($locationProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            # Log the delete
            $log = (new ProductLog())
                ->setUser($this->getUser())
                ->setProduct($locationProduct->getProduct())
                ->setCreated(new \DateTime('now'))
                ->setNote('Removed location product ('.$locationProduct->getId().') ['.$locationProduct->getLocation()->getAisle().'-'.$locationProduct->getLocation()->getRow().'-'.$locationProduct->getLocation()->getLevel().' x '. $locationProduct->getOnHand().'].');

            $em->persist($log);
            $em->remove($locationProduct);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Location was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Location');
        }
        
        # See if destination was passed (redirect)
        if ($request->get('destination') && !empty($request->get('destination')))
            return $this->redirect($request->get('destination'));

        # Default redirect
        return $this->redirect($this->generateUrl('product_edit',array('id'=>$locationProduct->getProduct()->getId())));
    }

    /**
     * Creates a form to delete a LocationProduct entity.
     *
     * @param Booking $locationProduct The LocationProduct entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(LocationProduct $locationProduct)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('location_product_delete', array('id' => $locationProduct->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Delete LocationProduct by id
     *
     * @Route("/delete/{id}", name="location_product_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(LocationProduct $locationProduct){
        $em = $this->getDoctrine()->getManager();
        $product = $locationProduct->getProduct();

        try {
            # Log the delete
            $log = (new ProductLog())
                ->setUser($this->getUser())
                ->setProduct($product)
                ->setCreated(new \DateTime('now'))
                ->setNote('Removed location product ('.$locationProduct->getId().') ['.$locationProduct->getLocation()->getAisle().'-'.$locationProduct->getLocation()->getRow().'-'.$locationProduct->getLocation()->getLevel().' x '. $locationProduct->getOnHand().'].');

            $em->persist($log);
            $em->remove($locationProduct);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Location Product was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Location Product');
        }

        # See if destination was passed (redirect)
        $request = new Request();
        if ($request->get('destination') && !empty($request->get('destination')))
            return $this->redirect($request->get('destination'));

        # Default redirect
        return $this->redirect($this->generateUrl('product_edit',array('id'=>$product->getId())));

    }

    /**
    * Bulk Action
    * @Route("/bulk-action/", name="location_product_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request)
    {
        print 'sdfdsf';
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('WarehouseBundle:LocationProduct');

                $removals = 0;
                foreach ($ids as $id) {
                    $locationProduct = $repository->find($id);
                    # Log the delete
                    $log = (new ProductLog())
                        ->setUser($this->getUser())
                        ->setProduct($locationProduct->getProduct())
                        ->setCreated(new \DateTime('now'))
                        ->setNote('Removed location ('.$locationProduct->getId().') ['.$locationProduct->getLocation()->getAisle().'-'.$locationProduct->getLocation()->getRow().'-'.$locationProduct->getLocation()->getLevel().' x '. $locationProduct->getOnHand().'].');

                    $em->persist($log);

                    $em->remove($locationProduct);
                    $em->flush();
                    $removals++;
                }

                $this->get('session')->getFlashBag()->add('success', $removals . ' Location product(s) were deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the locations ');
            }
        }

        # See if destination was passed (redirect)
        if ($request->get('destination') && !empty($request->get('destination')))
            return $this->redirect($request->get('destination'));

        # Default redirect
        return $this->redirect($this->generateUrl('location_product'));
    }


    /**
     * Lists all Booking entities.
     *
     * @Route("/ajax/create/{product_id}", name="location_product_new_ajax")
     * @Template()
     */
    public function createAjaxAction(Request $request, $product_id)
    {
        //This is optional. Do not do this check if you want to call the same action using a regular request.
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array('message' => 'You can access this only using Ajax!'), 400);
        }

        $em = $this->getDoctrine()->getManager();

        $product = $this->getDoctrine()->getRepository('WarehouseBundle:Product')->find($product_id);

        $locationProduct = (new LocationProduct())->setUser($this->getUser());
        $locationProduct->setProduct($product);
        $form = $this->createForm('WarehouseBundle\Form\LocationProductType',$locationProduct);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $location = $em->getRepository('WarehouseBundle:Location')->find($request->get('location_product')['location']);
            $location->setModified(new \DateTime('now'));
            $locationProduct->setLocation($location);
            $locationProduct->setStaged(0);
            $locationProductCheck = $em->getRepository('WarehouseBundle:LocationProduct')->findOneByProductAndLocation($product,$locationProduct->getLocation());
            if ($locationProductCheck) {
                # Already exists in this location (add)
                $locationProductCheck->setOnHand($locationProduct->getOnHand()+$locationProductCheck->getOnHand());
                $locationProductCheck->setStaged($locationProduct->getStaged());
                $locationProductCheck->setModified(new \DateTime("now"));
                $locationProduct = $locationProductCheck;
                unset($locationProductCheck);
            } else {
                $locationProduct->setCreated(new \DateTime("now"));
            }
            $locationProduct->getLocation()->setModified(new \DateTime('now'));
            //$location = $locationProduct->getLocation();
            //$location->setModified(new \DateTime('now'));

            # Log the save
            $log = (new ProductLog())
                ->setUser($this->getUser())
                ->setProduct($product)
                ->setCreated(new \DateTime('now'))
                ->setNote('Quantity set to '.$locationProduct->getOnHand(). ' at location '. $locationProduct->getLocation()->getAisle().'-'.$locationProduct->getLocation()->getRow().'-'.$locationProduct->getLocation()->getLevel().'.');

            $em->persist($log);
            $em->persist($locationProduct);
            $em->persist($location);
            $em->flush();

            //$data = $form->getData();
            $response['success'] = true;
            $response['message'] = 'Success!';
            $response['ajaxCommand'][] = array(
                'selector' => '.bulk_action .remove, #location_'.$locationProduct->getLocation()->getId(),
                'op' => 'remove',
                'value' => '',
            );
            $response['ajaxCommand'][] = array(
                'selector' => '.bulk_action tbody',
                'op' => 'append',
                'value' => $this->renderView('WarehouseBundle:LocationProduct:location_row.html.twig',array('locationProduct' => $locationProduct)),
            );
            $response['ajaxCommand'][] = array(
                'selector' => '.log_product tbody',
                'op' => 'prepend',
                'value' => $this->renderView('WarehouseBundle:ProductLog:log_row.html.twig',array('log' => $log)),
            );
            $response['ajaxCommand'][] = array(
                'selector' => '.log_product .remove',
                'op' => 'remove',
                'value' => '',
            );
            return new JsonResponse($response, 200);
        }

        return new JsonResponse(
            array(
                'success' => FALSE,
                'message' => 'Error',
                'errors' => $form->getErrors(),
                'form' => $this->renderView('WarehouseBundle:LocationProduct:ajax_location_form.html.twig',
                    array(
                        'product' => $product,
                        'entity' => null,
                        'form' => $form->createView(),
                    )
            )
        ), 400);
    }


}