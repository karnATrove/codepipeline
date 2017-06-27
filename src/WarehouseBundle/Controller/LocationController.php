<?php

namespace WarehouseBundle\Controller;

use Doctrine\ORM\QueryBuilder;
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

use BG\BarcodeBundle\Util\Base1DBarcode as barCode;

use WarehouseBundle\Entity\Location;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\ProductLog;
use WarehouseBundle\Form\LocationType;


#http://codemonkeys.be/2013/01/ajaxify-your-symfony2-forms-with-jquery/

/**
 * Location controller.
 *
 * @Route("/location")
 */
class LocationController extends Controller
{
	/**
	 * Lists all Location entities.
	 *
	 * @Route("/", name="location")
	 * @Method("GET")
	 */
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$queryBuilder = $em->getRepository('WarehouseBundle:Location')->createQueryBuilder('e');

		list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
		list($locations, $pagerHtml) = $this->paginator($queryBuilder, $request);

		return $this->render('WarehouseBundle::Location/index.html.twig', array(
			'locations' => $locations,
			'pagerHtml' => $pagerHtml,
			'filterForm' => $filterForm->createView(),

		));
	}

	/**
	 * Lists all Location entities.
	 *
	 * @Route("/batchinsert", name="batchinsert")
	 * @Method("GET")
	 */
	public function batchInsertAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		$aisles = ['E','F','G','H','I','J','K'];
		#$aisles = ['A','B','C'];
		#$aisles = ['D'];
		$aisles = ['L'];
		$num_rows = 24;
		$abc = 18;
		$d = 21;
		$num_rows = 24;
		foreach($aisles as $a) {
			for($x=1;$x<=$num_rows;$x++) {
				for($xx=1;$xx<=4;$xx++) {
					$ent = (new Location())
						->setAisle($a)
						->setRow($x)
						->setLevel($xx)
						->setStaging(FALSE)
						->setUser($this->getUser())
						->setCreated(new \DateTime('now'));
					$em->persist($ent);
				}
			}
		}
		$em->flush();

		$this->get('session')->getFlashBag()->add('success', "Successfully added batch locations." );

		return $this->render('WarehouseBundle:Default:dashboard.html.twig');
		return $this->redirect($this->generateUrl('location'));
	}

	/**
	 * Lists all Location entities.
	 *
	 * @Route("/batchlabels", name="batchlabel")
	 * @Method("GET")
	 */
	public function batchLabelAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('WarehouseBundle:Location');
		//$locations = $repository->findBy(array('level'=>1),array('aisle'=>'ASC','row'=>'ASC'));
		$locations = $repository->findBy(['aisle'=>'A'], array('aisle'=>'ASC','row'=>'ASC','level'=>'asc'));

		$sorted = array();
		usort($locations,function($a,$b) {
			if (intval($a->getRow()) == intval($b->getRow())) {
				if ($a->getLevel() == $b->getLevel()) {
					return 0;
				}
				return ($a->getLevel() < $b->getLevel()) ? -1 : 1;
			}
			return (intval($a->getRow()) < intval($b->getRow())) ? -1 : 1;
		});
		

		//$my2dBarcode = new matrixCode();
		$cnt = 0;
		$runlocations = array();

		/*
		foreach($locations as $location) {
			$barCode = new barCode();
			$barCode->savePath = $this->getBarcodeCachePath(false) . '/';
			$string = $location->getAisle(). '-'.$location->getRow().'-'.$location->getLevel().'-'.$location->getId();
			$locationBarCode = $barCode->getBarcodePNGPath($string, 'C128', 1.75, 45);
 			$bcHTMLRaw = $barCode->getBarcodeHTML($string, 'C128', 3, 140);
			$runlocations[] = (object)array(
				'location' => $location,
				'locationBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $locationBarCode),
				'raw' => $bcHTMLRaw,
			);
			$cnt++;
		}
		*/


		$aisles = ['E','F','G','H','I','J','K','L'];
		#$aisles = ['B','C'];
		#$aisles = ['D'];
		$num_rows = 24;
		$abc = 18;
		$d = 21;
		foreach($aisles as $a) {
			for($x=1;$x<=$num_rows;$x++) {
				for($xx=1;$xx<=4;$xx++) {
					$location = (object)array(
						'aisle' => $a,
						'row' => $x,
						'level' => $xx
					);

					$barCode = new barCode();
					$barCode->savePath = $this->getBarcodeCachePath(false) . '/';
					$string = $location->aisle.'-'.$location->row.'-'.$location->level;
					//$locationBarCode = $barCode->getBarcodePNGPath($string, 'C128', 1.75, 45);
		 			$bcHTMLRaw = $barCode->getBarcodeHTML($string, 'C128', 3.5, 140);
					$runlocations[] = (object)array(
						'location' => $location,
						//'locationBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $locationBarCode),
						'raw' => $bcHTMLRaw,
					);
				}
			}
		}



		$html = $this->renderView('WarehouseBundle::Location/pdf/labels.html.twig', [
			'locations' => $runlocations,
			//'locationBarCode' => str_replace($this->get('kernel')->getRootDir() . '/../web', '', $locationBarCode),
		]);
		//return new Response($html,200);

		$filename = 'locations.pdf';
		return new Response(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
			200,
			[
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
			]
		);
	}

	/**
	 * simple cache path returning method (sample cache path: "upload/barcode/cache" )
	 *
	 * @param bool $public
	 *
	 * @return string
	 *
	 */
	protected function getBarcodeCachePath($public = false)
	{

		return (!$public) ? $this->get('kernel')->getRootDir() . '/../web/uploads/barcode/cache' : '/uploads/barcode/cache';
	}

	/**
	 * Create filter form and process filter request.
	 *
	 */
	protected function filter(QueryBuilder $queryBuilder, Request $request)
	{
		$session = $request->getSession();
		$filterForm = $this->createForm('WarehouseBundle\Form\LocationFilterType');

		# Default sort
		if (empty($request->request->set('pcg_sort_col',''))) {
			$request->request->set('pcg_sort_col','aisle');
			$request->request->set('pcg_sort_order','asc');
		}

		// Reset filter
		if ($request->get('filter_action') == 'reset') {
			$session->remove('LocationControllerFilter');
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
				$session->set('LocationControllerFilter', $filterData);
			}
		} else {
			// Get filter from session
			if ($session->has('LocationControllerFilter')) {
				$filterData = $session->get('LocationControllerFilter');

				foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
					if (is_object($filter)) {
						$filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
					}
				}

				$filterForm = $this->createForm('WarehouseBundle\Form\LocationFilterType', $filterData);
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
			return $me->generateUrl('location', $requestParams);
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
	 * Displays a form to create a new Booking entity.
	 *
	 * @Route("/new", name="location_new")
	 * @Method({"GET", "POST"})
	 */
	public function newAction(Request $request)
	{
		$location = (new Location())->setUser($this->getUser());
		$location->setCreated(new \DateTime('now')); # Default created date
		$location->setStaging(FALSE);
		$form   = $this->createForm('WarehouseBundle\Form\LocationType', $location);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();

			# Additional validation
			$existing = $em->getRepository('WarehouseBundle:Location')->findOneBy(array('aisle'=>$location->getAisle(),'row'=>$location->getRow(),'level'=>$location->getLevel()));
			if (!$existing) {
				$em->persist($location);
				$em->flush();

				$editLink = $this->generateUrl('location_edit', array('id' => $location->getId()));
				$this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New location was created successfully.</a>" );

				$nextAction=  $request->get('submit') == 'save' ? 'location' : 'location_new';
				return $this->redirectToRoute($nextAction);
			} else {
				$this->get('session')->getFlashBag()->add('error', 'Location already exists '. $existing->getId());
			}
		}
		return $this->render('WarehouseBundle::Location/new.html.twig', array(
			'location' => $location,
			'form'   => $form->createView(),
		));
	}


	/**
	 * Displays a form to edit an existing Location entity.
	 *
	 * @Route("/{id}/edit", name="location_edit")
	 * @Method({"GET", "POST"})
	 */
	public function editAction(Request $request, Location $location)
	{
		$original_location = clone $location; # Store for logging
		$deleteForm = $this->createDeleteForm($location);
		$editForm = $this->createForm('WarehouseBundle\Form\LocationType', $location);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {

			$this->get('session')->getFlashBag()->add('error', 'Editing not allowed at this time.');
			return $this->redirectToRoute('location_edit', array('id' => $location->getId()));

//            $em = $this->getDoctrine()->getManager();
//
//            # Log the save
//            $log = (new ProductLog())
//                ->setUser($this->getUser())
//                ->setProduct($location->getProduct())
//                ->setCreated(new \DateTime('now'))
//                ->setNote('Location ('.$location->getId().') ['.$original_location->getAisle().'-'.$original_location->getRow().'-'.$original_location->getLevel().' x '. $original_location->getOnHand().'] changed to ['.$location->getAisle().'-'.$location->getRow().'-'.$location->getLevel().' x '. $location->getOnHand().'].');
//
//            $em->persist($log);
//            $em->persist($location);
//            $em->flush();
//
//            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
//            return $this->redirectToRoute('location_edit', array('id' => $location->getId()));
		}
		return $this->render('WarehouseBundle::Location/edit.html.twig', array(
			'location' => $location,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}


	/**
	 * Deletes an Location entity.
	 *
	 * @Route("/{id}", name="location_delete")
	 * @Method("DELETE")
	 */
	public function deleteAction(Request $request, Location $location)
	{

		# Prohibit deletions
		throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Deletion of locations is prohibited.');
		# Default redirect
//        return $this->redirect($this->generateUrl('location'));

//        $form = $this->createDeleteForm($location);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//
//            # Log the delete
//            $log = (new ProductLog())
//                ->setUser($this->getUser())
//                ->setProduct($location->getProduct())
//                ->setCreated(new \DateTime('now'))
//                ->setNote('Removed location ('.$location->getId().') ['.$location->getAisle().'-'.$location->getRow().'-'.$location->getLevel().' x '. $location->getOnHand().'].');
//
//            $em->persist($log);
//            $em->remove($location);
//            $em->flush();
//            $this->get('session')->getFlashBag()->add('success', 'The Location was deleted successfully');
//        } else {
//            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Location');
//        }
//
//        # See if destination was passed (redirect)
//        if ($request->get('destination') && !empty($request->get('destination')))
//            return $this->redirect($request->get('destination'));
//
//        # Default redirect
//        return $this->redirect($this->generateUrl('location'));
	}


	/**
	 * Creates a form to delete a Location entity.
	 *
	 * @param Location $location The Location entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(Location $location)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('location_delete', array('id' => $location->getId())))
			->setMethod('DELETE')
			->getForm();
	}


	/**
	 * Delete Location by id
	 *
	 * @Route("/delete/{id}", name="location_by_id_delete")
	 * @Method("GET")
	 */
	public function deleteByIdAction(Location $location){
		$em = $this->getDoctrine()->getManager();
		$products = $location->getProducts();

		# Prohibit deletions
		throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Deletion of locations is prohibited.');
		# Default redirect
//        return $this->redirect($this->generateUrl('location'));
//
//        try {
//            # Validate location is empty
//            if (count($products)) {
//                throw new \Exception('Location is not empty.');
//            }
//
//            # Log the delete
//            $log = (new ProductLog())
//                ->setUser($this->getUser())
//                ->setProduct($location->getProduct())
//                ->setCreated(new \DateTime('now'))
//                ->setNote('Removed location ('.$location->getId().') ['.$location->getAisle().'-'.$location->getRow().'-'.$location->getLevel().' x '. $location->getOnHand().'].');
//
//            $em->persist($log);
//            $em->remove($location);
//            $em->flush();
//            $this->get('session')->getFlashBag()->add('success', 'The Location was deleted successfully');
//        } catch (Exception $ex) {
//            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Location');
//        }
//
//        # See if destination was passed (redirect)
//        $request = new Request();
//        if ($request->get('destination') && !empty($request->get('destination')))
//            return $this->redirect($request->get('destination'));
//
//        # Default redirect
//        return $this->redirect($this->generateUrl('location'));

	}


	/**
	 * Bulk Action
	 * @Route("/bulk-action/", name="location_bulk_action")
	 * @Method("POST")
	 */
	public function bulkAction(Request $request)
	{
		$ids = $request->get("ids", array());
		$action = $request->get("bulk_action", "delete");

		if ($action == "delete") {
			# Prohibit deletions
			throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Deletion of locations is prohibited.');
			# Default redirect
//            return $this->redirect($this->generateUrl('location'));
//
//            try {
//                $em = $this->getDoctrine()->getManager();
//                $repository = $em->getRepository('WarehouseBundle:Location');
//
//                foreach ($ids as $id) {
//                    $location = $repository->find($id);
//                    # Log the delete
//                    $log = (new ProductLog())
//                        ->setUser($this->getUser())
//                        ->setProduct($location->getProduct())
//                        ->setCreated(new \DateTime('now'))
//                        ->setNote('Removed location ('.$location->getId().') ['.$location->getAisle().'-'.$location->getRow().'-'.$location->getLevel().' x '. $location->getOnHand().'].');
//
//                    $em->persist($log);
//
//                    $em->remove($location);
//                    $em->flush();
//                }
//
//                $this->get('session')->getFlashBag()->add('success', 'locations was deleted successfully!');
//
//            } catch (Exception $ex) {
//                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the locations ');
//            }
		}

		# See if destination was passed (redirect)
		if ($request->get('destination') && !empty($request->get('destination')))
			return $this->redirect($request->get('destination'));

		# Default redirect
		return $this->redirect($this->generateUrl('location'));
	}


	/**
	 * Lists all Booking entities.
	 *
	 * @Route("/ajax/create/{product_id}", name="location_new_ajax")
	 * @Template()
	 */
	public function createAjaxAction(Request $request, $product_id)
	{
		//This is optional. Do not do this check if you want to call the same action using a regular request.
		if (!$request->isXmlHttpRequest()) {
			return new JsonResponse(array('message' => 'You can access this only using Ajax!'), 400);
		}

		$product = $this->getDoctrine()->getRepository('WarehouseBundle:Product')->find($product_id);

		//$request = $this->getRequest(); // replacement for getting the request
		$location = (new \WarehouseBundle\Entity\Location())->setUser($this->getUser());
		$location->setProduct($product);
		$form = $this->createForm('WarehouseBundle\Form\LocationType',$location);
		$form->handleRequest($request);

		if ($form->isValid()) {

			$location->setCreated(new \DateTime("now"));

			$em = $this->getDoctrine()->getManager();

			# Log the save
			$log = (new ProductLog())
				->setUser($this->getUser())
				->setProduct($product)
				->setCreated(new \DateTime('now'))
				->setNote('New location created @ ['.$location->getAisle().'-'.$location->getRow().'-'.$location->getLevel().' x '. $location->getOnHand().'].');

			$em->persist($log);
			$em->persist($location);
			$em->flush();

			//$data = $form->getData();
			$response['success'] = true;
			$response['message'] = 'Success!';
			$response['ajaxCommand'][] = array(
				'selector' => '.bulk_action tbody',
				'op' => 'append',
				'value' => $this->renderView('WarehouseBundle:Location:location_row.html.twig',array('location' => $location)),
			);
			$response['ajaxCommand'][] = array(
				'selector' => '.bulk_action .remove',
				'op' => 'remove',
				'value' => '',
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
				'form' => $this->renderView('WarehouseBundle:Location:ajax_location_form.html.twig',
					array(
						'booking' => $product,
						'entity' => $location,
						'form' => $form->createView(),
					)
				)
			), 400);
	}


}