<?php

namespace WarehouseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use WarehouseBundle\Form\BookingProductType;
use WarehouseBundle\Form\IncomingProductType;
use WarehouseBundle\Form\IncomingProductScanType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\IncomingProductScan;
use WarehouseBundle\Manager\IncomingManager;

/**
 * Scan controller.
 *
 * @Route("/scan")
 */
class ScanController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/", name="scan")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('scan'))
            ->setMethod('POST')
            ->add('scan', TextType::class, array(
                'attr'=>array(
                    'placeholder' => 'Scan',
                ),
            ))
            ->add('mode', HiddenType::class, array(
                'empty_data' => 'auto',
            ))
            ->add('incoming', EntityType::class, array(
                'class' => 'WarehouseBundle:Incoming',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->where('i.status IN (:status)')
                        ->setParameter('status',array(2));   # Arrived
                },
                'placeholder' => 'Choose incoming',
                'choice_label' => 'name',
            ))
            ->getForm();
        $form->handleRequest($request);

        # Ajax response array
        $response = array();

        //if ($this->getRequest()->isMethod('POST')) {} # Might have to use this in Symfony 3x
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $search_string = $request->request->get('form')['scan'];
            $search_mode = $request->request->get('form')['mode'];

            switch($search_mode) {
                case 'stock':
                    # No search string, we use a selected incoming list
                    if (isset($request->request->get('form')['incoming'])) {
                        $response = $this->scanModeStock($request->request->get('form')['incoming']);
                    }
                    break;
                case 'pick':
                    $response = $this->scanModePick($search_string);
                    break;
                case 'inventory':
                    $response = $this->scanModeInventory($search_string);
                    break;
                case 'order':
                    $response = $this->scanModeOrder($search_string);
                    break;
                case 'return':
                    $response = $this->scanModeReturn($search_string);
                    break;
                case 'auto':
                default:
                    # Booking Pick Mode to be activeated if entry
                    # is in the format of BO0000099999
                    if (preg_match('/^BO[0-9]{10}$/',$search_string)) {
                        $response = $this->scanModePick($search_string);
                    } else {
                        $response = $this->scanModeAuto($search_string);
                    }
                    break;
            }

            $response['ajaxCommand'][] = array(
                'selector' => '#quick-scan .loading',
                'op' => 'hide',
                'value' => '',
            );
            return new JsonResponse($response, 200);
        } elseif ($form->isSubmitted()) {
            return new JsonResponse(
                array(
                    'success' => FALSE,
                    'message' => 'Error',
                    'error' => 'There was an error with what you scanned.',
                ), 400);
        }

        return $this->render('WarehouseBundle::Scan/index.html.twig', array(
            'form' => $form->createView(),
        ));
    }



    public function scanModeAuto($search_string) {
        $em = $this->getDoctrine()->getManager();
        # Lets do the search
        $products = $em->getRepository('WarehouseBundle:Product')->findByModel($search_string,10);
        $bookings = $em->getRepository('WarehouseBundle:BookingProduct')->findByModelOrOrder($search_string,10);
        $incoming = $em->getRepository('WarehouseBundle:IncomingProduct')->findByModelOrName($search_string,10);
        $returns = $em->getRepository('WarehouseBundle:BookingReturn')->findByModelOrOrder($search_string,10);

        # Response back with matched results
        $response = array();
        $response['ajaxCommand'][] = array(
            'selector' => '#scan-result',
            'op' => 'html',
            'value' => $this->renderView('WarehouseBundle::Scan/scan_result.html.twig', array(
                'search' => $search_string,
                'product' => array('count' => count($products),'results'=>$products),
                'booking' => array('count' => count($bookings),'results'=>$bookings),
                'incoming' => array('count' => count($incoming),'results'=>$incoming),
                'return' => array('count' => count($returns),'results'=>$returns),
            )),
        );

        return $response;
    }

    public function scanModeStock($incomingId) {
        $response = array();
        $em = $this->getDoctrine()->getManager();
        $incoming = $em->getRepository('WarehouseBundle:Incoming')->find($incomingId);
        if (!$incoming || !IncomingManager::isActive($incoming)) {
            $response['ajaxCommand'][] = array(
                'selector' => '#scan-result',
                'op' => 'html',
                'value' => $this->renderView('WarehouseBundle::Scan/stock/error.html.twig', array(
                    'search' => $incomingId,
                    'error' => 'Container/forward not found or the container/forward is not in an active status.',
                )),
            );
            return $response;
        }

        // Scan sku form and submit via ajax
        $form = $this->createIncomingItemStockForm(Request::createFromGlobals(),$incoming);

        // IncomingProductScan item form
        $form_scan = $this->createModifyForm($incoming);

        $html = $this->renderView('WarehouseBundle::Scan/stock/incoming.html.twig', array(
            'form' => $form->createView(),
            'form_scan' => $form_scan->createView(),
            'search' => $incomingId,
            'incoming' => $incoming,
        ));

        $response['ajaxCommand'][] = array(
            'selector' => '#scan-result',
            'op' => 'html',
            'value' => $html,
        );

        return $response;
    }

    public function scanModePick($search_string) {
        $em = $this->getDoctrine()->getManager();
        $orderNumber = preg_replace('/^BO[0]+/','',$search_string);
        $html = $notice = ''; # html output
        $qb = $em->createQueryBuilder();
        $qb->select('b')
           ->from('WarehouseBundle:Booking','b')
           ->where('b.orderNumber = :orderNumber AND b.status > :status')
           ->setParameter('orderNumber', $orderNumber)
           ->setParameter('status',0)
           ->setMaxResults(1);
        try {
            $booking = $qb->getQuery()->getSingleResult();
            if (!$booking->getPickingFlag()) {
                # This shouldnt be available as it isnt flagged.
                //$notice = 'This Booking Order should be flagged for picking.';
            }

            # Create form for generating picking quantities
            $form = $this->createBookingProductPickForm(Request::createFromGlobals(),$booking);

            $html = $this->renderView('WarehouseBundle::Scan/booking/pick.html.twig', array(
                'form' => $form->createView(),
                'search' => $search_string,
                'booking' => $booking,
                'notice' => $notice,
            ));
        } catch (\Exception $e) {
            $html = $this->renderView('WarehouseBundle::Scan/booking/error.html.twig', array('search' => $search_string,'error' => $e->getMessage()));
        }
        $response = array();
        $response['ajaxCommand'][] = array(
            'selector' => '#scan-result',
            'op' => 'html',
            'value' => $html,
        );

        return $response;
    }

    public function scanModeInventory($search_string) {
        $html = $notice = ''; # html output

        $form = $this->createInventoryStockForm(Request::createFromGlobals());
        $html = $this->renderView('WarehouseBundle::Scan/inventory/location.html.twig', array(
            'form' => $form->createView(),
            'search' => $search_string,
            'notice' => $notice,
        ));
        $response = array();
        $response['ajaxCommand'][] = array(
            'selector' => '#scan-result',
            'op' => 'html',
            'value' => $html,
        );

        return $response;

        $em = $this->getDoctrine()->getManager();
        $orderNumber = preg_replace('/^BO[0]+/','',$search_string);
        $html = $notice = ''; # html output
        $qb = $em->createQueryBuilder();
        $qb->select('b')
           ->from('WarehouseBundle:Booking','b')
           ->where('b.orderNumber = :orderNumber AND b.status > :status')
           ->setParameter('orderNumber', $orderNumber)
           ->setParameter('status',0)
           ->setMaxResults(1);
        try {
            $booking = $qb->getQuery()->getSingleResult();
            if (!$booking->getPickingFlag()) {
                # This shouldnt be available as it isnt flagged.
                //$notice = 'This Booking Order should be flagged for picking.';
            }

            # Create form for generating picking quantities
            $form = $this->createBookingProductPickForm(Request::createFromGlobals(),$booking);

            $html = $this->renderView('WarehouseBundle::Scan/booking/pick.html.twig', array(
                'form' => $form->createView(),
                'search' => $search_string,
                'booking' => $booking,
                'notice' => $notice,
            ));
        } catch (\Exception $e) {
            $html = $this->renderView('WarehouseBundle::Scan/booking/error.html.twig', array('search' => $search_string,'error' => $e->getMessage()));
        }
        $response = array();
        $response['ajaxCommand'][] = array(
            'selector' => '#scan-result',
            'op' => 'html',
            'value' => 'dsfs'.$html,
        );

        return $response;
    }

    public function scanModeOrder($search_string) {
        $em = $this->getDoctrine()->getManager();

        # Lets do the search
        $bookings = $em->getRepository('WarehouseBundle:Booking')->findByModelOrOrder($search_string,10);

        # Response back with matched results
        $response = array();
        $html = $this->renderView('WarehouseBundle::Scan/booking/order.html.twig', array(
            //'form' => $form->createView(),
            'search' => $search_string,
            'bookings' => $bookings,
        ));
        $response['ajaxCommand'][] = array(
            'selector' => '#scan-result',
            'op' => 'html',
            'value' => $html,
        );

        return $response;
    }

    public function scanModeReturn($search_string) {

    }



    /**
     * Lists all User entities.
     *
     * @Route("/product", name="scan_product")
     * @Template()
     */
    public function productAction(Request $request)
    {

    }













    /**
     * Submission of Booking Product Picking Entities.
     *
     * @Route("/booking/ajax/{id}", name="scan_booking_ajax")
     * @Template()
     */
    public function formAjaxBookingProductPickForm(Request $request, Booking $booking) {
        $bookingManager = $this->get('BookingManager');

        $form = $this->createBookingProductPickForm($request,$booking);

        # Update the booking (products)
        $form->handleRequest($request);

        $response = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setModified(new \DateTime('now'));

            # See if any picks were set
            foreach($booking->getProducts() as $key => $bookingProduct) {
                $locations = $request->request->get('form')['products'][$key]['location'];
                foreach($locations as $location_product_id => $qty_picked) {
                    if ($qty_picked > 0) {
                        $locationProduct = $this->getDoctrine()->getRepository('WarehouseBundle:LocationProduct')->find($location_product_id);
                        # Create BookingProductLocation()
                        $bookingManager->createPick($bookingProduct,$locationProduct,$qty_picked);
                    }
                }
            }

            # Note Booking status is updated by a hidden field and jQuery
            $bookingManager->updateBooking($booking,TRUE);

            # Ajax response
            $response['success'] = true;
            $response['ajaxCommand'][] = array(
                'selector' => '#scan-result',
                'op' => 'html',
                'value' => $this->renderView('WarehouseBundle::Scan/booking/complete.html.twig', array('booking' => $booking)),
            );
        } else {
            $response['ajaxCommand'][] = array(
                'selector' => '#scan-result',
                'op' => 'prepend',
                'value' => $this->renderView('WarehouseBundle::Scan/booking/error.html.twig', array('search' => $booking->getOrderNumber(),'error' => 'There was an issue trying to submit the form.')),
            );
        }

        return new JsonResponse($response, 200);
    }


    /**
     * Lists all Incoming Scanned entities.
     *
     * @Route("/incoming/ajax/{id}", name="scan_stock_ajax")
     * @Template()
     */
    public function formAjaxStockIncomingForm(Request $request, Incoming $incoming) {
        $em = $this->getDoctrine()->getManager();

        # Create the original form from the scan so we can handle its submit...
        $form = $this->createIncomingItemStockForm($request,$incoming);

        # Adjust the Incoming object
        $form->handleRequest($request);

        $response = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $model = trim($request->request->get('form')['scanIncoming']);

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
            $item->setModified(new \DateTime('now'));
            $em->persist($item);
            $em->flush();


            $response['success'] = true;
            $response['ajaxCommand'][] = array(
                'selector' => '.incomingScannedProducts',
                'op' => 'html',
                'value' => $this->renderView('WarehouseBundle::Scan/stock/product_scans.html.twig', array(
                    'form' => $this->createModifyForm($incoming)->createView(),
                    'incoming' => $incoming,
                )),
            );
            $response['ajaxCommand'][] = array(
                'selector' => '#quick-scan .loading',
                'op' => 'hide',
                'value' => '',
            );
        } elseif ($form->isSubmitted()) {
            $response['success'] = false;
        }
        return new JsonResponse($response,200);
    }

    /**
     * Lists all Incoming Scanned entities.
     *
     * @Route("/inventory/ajax", name="scan_inventory_ajax")
     * @Template()
     */
    public function formAjaxInventoryForm(Request $request) {
        $em = $this->getDoctrine()->getManager();

        # Create the original form from the scan so we can handle its submit...
        $form = $this->createIncomingItemStockForm($request,$incoming);

        # Adjust the Incoming object
        $form->handleRequest($request);

        $response = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $model = trim($request->request->get('form')['scanIncoming']);

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
            $item->setModified(new \DateTime('now'));
            $em->persist($item);
            $em->flush();


            $response['success'] = true;
            $response['ajaxCommand'][] = array(
                'selector' => '.incomingScannedProducts',
                'op' => 'html',
                'value' => $this->renderView('WarehouseBundle::Scan/stock/product_scans.html.twig', array(
                    'form' => $this->createModifyForm($incoming)->createView(),
                    'incoming' => $incoming,
                )),
            );
            $response['ajaxCommand'][] = array(
                'selector' => '#quick-scan .loading',
                'op' => 'hide',
                'value' => '',
            );
        } elseif ($form->isSubmitted()) {
            $response['success'] = false;
        }
        return new JsonResponse($response,200);
    }

    /**
     * Ajax deletion of an IncomingProductScan item.
     *
     * @Route("/incoming-delete/ajax/{id}", name="scan_stock_delete_ajax")
     * @Template()
     */
    public function formAjaxStockIncomingDeleteScan(Request $request, IncomingProductScan $incomingProductScan) {
        $em = $this->getDoctrine()->getManager();

        $response = array();
        $incoming = $incomingProductScan->getIncoming();
        if ($incoming->getStatus(1,2)) { # Inbound or Arrived
            $em->remove($incomingProductScan);
            $em->flush();
            # This form needs to replicate the below function
            $form_scan = $this->createModifyForm($incoming);
            $html = $this->renderView('WarehouseBundle::Scan/stock/product_scans.html.twig', array(
                'form' => $form_scan->createView(),
                'incoming' => $incoming,
            ));
            $response['success'] = true;
            $response['ajaxCommand'][] = array(
                'selector' => '.incomingScannedProducts',
                'op' => 'html',
                'value' => $html,
            );
            $response['ajaxCommand'][] = array(
                'selector' => '#quick-scan .loading',
                'op' => 'hide',
                'value' => '',
            );
        } else {
            # Incoming is not in the right status
            $response['success'] = false;
            $response['ajaxCommand'][] = array(
                'selector' => '.error_zone',
                'op' => 'html',
                'value' => '<div class="alert alert-error"><p><strong>Error</strong> incoming container is no longer in active/arrived status.</p></div>',
            );
            $response['ajaxCommand'][] = array(
                'selector' => '#quick-scan .loading',
                'op' => 'hide',
                'value' => '',
            );
        }
        return new JsonResponse($response,200);
    }

    /**
     * Saves changes to IncomingProductScan items
     *
     * @Route("/incoming-scanned/ajax/{id}", name="scan_stock_product_ajax")
     * @Template()
     */
    public function formAjaxStockIncomingProductForm(Request $request, Incoming $incoming) {
        $form = $this->createModifyForm($incoming);
        $form->handleRequest($request);

        $response = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $items = $form->getData();
            $em = $this->getDoctrine()->getManager();
            foreach($items as $item) {
                $item->setModified(new \DateTime('now'));
            }
            $em->persist($items);
            $em->flush();
            $response['success'] = true;

            // Is COMPLETE clicked
            if ($form->get('complete')->isClicked()) {
            	try{
            		$this->get('warehouse.workflow.incoming_workflow')->setIncomingComplete($incoming);
		            $this->get('session')->getFlashBag()->add('success', "Incoming container scanned list was saved and Incoming container is now complete." );
		            $response['ajaxCommand'][] = array(
			            'selector' => '#scan-result .x_content',
			            'op' => 'html',
			            'value' => '<div class="alert alert-success">Incoming products were successfully assigned to inventory and Incoming container was set to completed.</div>',
		            );
		            $response['ajaxCommand'][] = array(
			            'selector' => '#form_incoming [value="'.$incoming->getId().'"]',
			            'op' => 'remove',   # could be 'attr' so we can just disable it
			            'value' => array(
				            'disabled' => 'disabled',
			            ),
		            );
		            $response['ajaxCommand'][] = array(
			            'selector' => '#form_scanIncoming',
			            'op' => 'remove',
			            'value' => '',
		            );
	            }catch (\Exception $exception){
		            $this->get('session')->getFlashBag()->add('error', "An error occured while trying to set incoming to complete." );
		            $response['ajaxCommand'][] = array(
			            'selector' => '.error_zone',
			            'op' => 'html',
			            'value' => '<div class="alert alert-error"><p><strong>Error:</strong> An error occured while trying to set incoming to complete.</p></div>',
		            );
	            }
            } else {
                $response['ajaxCommand'][] = array(
                    'selector' => '.incomingScannedProducts',
                    'op' => 'html',
                    'value' => $this->renderView('WarehouseBundle::Scan/stock/product_scans.html.twig', array(
                        'form' => $form->createView(),
                        'incoming' => $incoming,
                    )),
                );
            }
        } else {
            $response['ajaxCommand'][] = array(
                'selector' => '.error_zone',
                'op' => 'html',
                'value' => '<div class="alert alert-error"><p><strong>Error:</strong> could not submit the form.</p></div>',
            );
        }

        $response['ajaxCommand'][] = array(
            'selector' => '#quick-scan .loading',
            'op' => 'hide',
            'value' => '',
        );

        return new JsonResponse($response, 200);
    }

	/**
	 * Creates a form to modify a Incoming with IncomingProductScan entity.
	 *
	 * @param Incoming $incoming
	 *
	 * @return mixed
	 */
    function createModifyForm(Incoming $incoming) {
         $form = $this->createFormBuilder($incoming)
            ->setAction($this->generateUrl('scan_stock_product_ajax', array('id' => $incoming->getId())))
            ->setMethod('POST')
            ->add('incomingScannedProducts',CollectionType::class,array(
                'entry_type' => IncomingProductScanType::class,
                'entry_options' => array(
                    'attr' => array('class'=>'form-control'),
                ),
                'allow_add' => TRUE,
                'allow_delete' => TRUE,
                'prototype' => TRUE,
                'by_reference' => TRUE,
            ))
            ->add('complete', SubmitType::class, array(
                'label' => 'COMPLETE SCAN',
                'attr' => array(
                    'class' => 'btn btn-success btn-large',
                    'data-confirm' => 'This will mark the container are closed. It will also assign all items to active inventory. Are you sure you are complete?',
                   // 'onclick' => 'return confirm(\'This will mark the container are closed. It will also assign all items to active inventory.\n\nAre you sure you are complete?\',\'Please confirm\');',
                )
            ))
            ->getForm();

        return $form;
    }

    /**
     * Form that will generate picking options for each booking product.
     *
     * @param      \Symfony\Component\HttpFoundation\Request  $request  The request
     * @param      \WarehouseBundle\Entity\Booking            $booking  The booking
     *
     * @return     <type>                                     ( description_of_the_return_value )
     */
    public function createBookingProductPickForm(Request $request, Booking $booking) {
        return $this->createFormBuilder($booking)
            ->setAction($this->generateUrl('scan_booking_ajax', array('id' => $booking->getId())))
            ->setMethod('POST')
            ->add('products',CollectionType::class,array(
                'entry_type' => BookingProductType::class,
                'entry_options' => array(
                    'attr' => array('class'=>'form-control'),
                ),
            ))
            ->add('status', HiddenType::class)
            ->getForm();
    }

    /**
     * Creates an incoming item stock form.
     *
     * @param      \Symfony\Component\HttpFoundation\Request  $request   The request
     * @param      \WarehouseBundle\Entity\Incoming           $incoming  The incoming
     *
     * @return     <type>                                     ( description_of_the_return_value )
     */
    public function createIncomingItemStockForm(Request $request, Incoming $incoming) {
        return $this->createFormBuilder()
            ->setMethod('POST')
            # Set url to ajax create scan item
            ->setAction($this->generateUrl('scan_stock_ajax', array('id' => $incoming->getId())))
            ->add('scanIncoming', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Scan container item.',
                    'class' => 'form-control col-xs-12',
                    'id' => 'scan-incoming',
                ),
            ))->getForm();
    }

    /**
     * [createInventoryStockForm description]
     * @param  Request $request [description]
     * @return [type]           [description]
     *
     * @return     <type>                                     ( description_of_the_return_value )
     */
    public function createInventoryStockForm(Request $request) {
        return $this->createFormBuilder()
            ->setMethod('POST')
            ->setAction($this->generateUrl('scan_inventory_ajax'))
            ->add('scanInventory', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Scan an item.',
                    'class' => 'form-control col-xs-12',
                    'id' => 'scan-inventory',
                )
            ))
            ->add('scanLocation', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Select location',
                    'class' => 'form-control col-xs-12',
                    'id' => 'scan-location',
                )
            ))->getForm();
    }

}
