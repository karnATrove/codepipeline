<?php

namespace WarehouseBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use BG\BarcodeBundle\Util\Base1DBarcode as barCode;
use BG\BarcodeBundle\Util\Base2DBarcode as matrixCode;

use WarehouseBundle\Form\IncomingFileType;

use WarehouseBundle\Entity\Incoming;
use WarehouseBundle\Entity\IncomingFile;
use WarehouseBundle\Entity\IncomingProduct;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Form\IncomingType;

/**
 * Booking controller.
 *
 * @Route("/incoming")
 */
class IncomingController extends Controller
{
	/**
	 * Lists all Incoming entities.
	 *
	 * @Route("/", name="incoming")
	 * @Method("GET")
	 */
	public function indexAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$queryBuilder = $em->getRepository('WarehouseBundle:Incoming')->createQueryBuilder('i');

		list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
		list($incoming, $pagerHtml) = $this->paginator($queryBuilder, $request);

		return $this->render('incoming/index.html.twig', [
			'incoming' => $incoming,
			'pagerHtml' => $pagerHtml,
			'filterForm' => $filterForm->createView(),

		]);
	}


	/**
	 * Create filter form and process filter request.
	 *
	 * @param QueryBuilder $queryBuilder
	 * @param Request      $request
	 *
	 * @return array
	 */
	protected function filter($queryBuilder, Request $request)
	{
		$session = $request->getSession();
		$filterForm = $this->createForm('WarehouseBundle\Form\IncomingFilterType');

		// Reset filter
		if ($request->get('filter_action') == 'reset') {
			$session->remove('IncomingControllerFilter');
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
				$session->set('IncomingControllerFilter', $filterData);
			}
		} else {
			// Get filter from session
			if ($session->has('IncomingControllerFilter')) {
				$filterData = $session->get('IncomingControllerFilter');

				foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
					if (is_object($filter)) {
						$filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
					}
				}

				$filterForm = $this->createForm('WarehouseBundle\Form\IncomingFilterType', $filterData);
				$this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
			}
		}

		return [$filterForm, $queryBuilder];
	}


	/**
	 * Get results from paginator and get paginator view.
	 *
	 */
	protected function paginator(QueryBuilder $queryBuilder, Request $request)
	{
		//sorting
		$sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
		$queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
		// Paginator
		$adapter = new DoctrineORMAdapter($queryBuilder);
		$pagerfanta = new Pagerfanta($adapter);
		$pagerfanta->setMaxPerPage($request->get('pcg_show', 20));

		try {
			$pagerfanta->setCurrentPage($request->get('pcg_page', 1));
		} catch (OutOfRangeCurrentPageException $ex) {
			$pagerfanta->setCurrentPage(1);
		}

		$entities = $pagerfanta->getCurrentPageResults();

		// Paginator - route generator
		$me = $this;
		$routeGenerator = function ($page) use ($me, $request) {
			$requestParams = $request->query->all();
			$requestParams['pcg_page'] = $page;
			return $me->generateUrl('incoming', $requestParams);
		};

		// Paginator - view
		$view = new TwitterBootstrap3View();
		$pagerHtml = $view->render($pagerfanta, $routeGenerator, [
			'proximity' => 3,
			'prev_message' => 'previous',
			'next_message' => 'next',
		]);

		return [$entities, $pagerHtml];
	}


	/**
	 * Displays a form to create a new Incoming entity.
	 *
	 * @Route("/new", name="incoming_new")
	 * @Method({"GET", "POST"})
	 */
	public function newAction(Request $request)
	{

		$incoming = (new Incoming())->setUser($this->getUser());
		$form = $this->createForm(IncomingType::class, $incoming);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$incoming->setCreated(new \DateTime('now'));

			$em->persist($incoming);
			$em->flush();

			$editLink = $this->generateUrl('incoming_edit', ['id' => $incoming->getId()]);
			$this->get('session')
				->getFlashBag()
				->add('success', "<a href='$editLink'>New incoming was created successfully.</a>");

			if ($request->get('submit') == 'save')
				return $this->redirectToRoute('incoming');
			return $this->redirectToRoute('incoming_new_import', ['id' => $incoming->getId()]);
		}
		return $this->render('incoming/new.html.twig', [
			'incoming' => $incoming,
			'form' => $form->createView(),
		]);
	}

	/**
	 * Displays a form to create a new Incoming entity.
	 *
	 * @Route("/{incoming}/importfile/{id}", name="incoming_import_file")
	 * @Method({"GET", "POST"})
	 */
	public function importFileAction(Request $request, Incoming $incoming, IncomingFile $incomingFile)
	{
		# A little validation
		if (count($incoming->getIncomingProducts())) {
			$this->get('session')->getFlashBag()->add('error', "Please remove all incoming products from " . $incoming->getName() . " before trying to re-import.");
			return $this->redirectToRoute('incoming_products', ['incoming_id' => $incoming->getId()]);
		}

		$em = $this->getDoctrine()->getManager();

		// ask the service for a Excel5
		$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($this->get('kernel')->getRootDir() . '/../web/uploads/imports/' . $incomingFile->getFilepath());
		$sheetData = $phpExcelObject->getActiveSheet()->toArray(null, true, true, true);
		$imports = 0;

		# First loop for creating non existing products
		$queued_products = []; # Insert only once
		foreach ($sheetData as $row => $data) {
			if (is_numeric($data['A'])) { # Indicates the entry row number
				$model = strtoupper(trim($data['B']));
				$name = trim($data['C']);
				$qty = intval(trim($data['D']));
				$ctns = intval(trim($data['E']));
				# Sometimes ctns is not set because of a parent product
				# So default by using the assigned quantity..
				if (!$ctns) {
					$ctns = $qty;
				}

				# Find the product
				$product = $em->getRepository('WarehouseBundle:Product')->findOneByModel($model);
				if (!$product && !in_array($model, $queued_products)) {
					# Create new product
					$product = (new Product())->setUser($this->getUser())
						->setModel($model)
						->setDescription(!empty($name) ? $name : 'Unstated')
						->setCreated(new \DateTime('now'))
						->setStatus(1)# default active
						->setDimUnits('in')# default inches
						->setWeightUnits('lbs')# default lbs
						->setQtyPerCarton(intval($qty) / intval($ctns));
					$queued_products[] = $model;
					$em->persist($product);

					$this->get('session')->getFlashBag()->add('success', "Created new product (" . $model . '").');
				}
			}
		}
		$em->flush();
		
		$savedIncomingProducts=[];
		# Second loop to create incomingProduct
		foreach ($sheetData as $row => $data) {
			if (is_numeric($data['A'])) { # Indicates the entry row number
				$model = strtoupper(trim($data['B']));
				$qty = intval(trim($data['D']));

				if (key_exists($model,$savedIncomingProducts)){
					$incomingProduct = $savedIncomingProducts[$model];
					$incomingProduct->setQty($qty + $incomingProduct->getQty())
						->setModified(new \DateTime())
						->setUser($this->getUser());
				} else {
					$incomingProduct = new IncomingProduct();
					$incomingProduct->setUser($this->getUser())
						->setIncoming($incoming)
						->setQty($qty)
						->setModel($model)
						->setModified(new \DateTime())
						->setCreated(new \DateTime());

					# Find the product
					$product = $em->getRepository('WarehouseBundle:Product')->findOneByModel($model);
					if (!$product) {
						throw new \Exception('Product model failed to import and could not be identified for incomingProduct');
					}

					$incomingProduct->setProduct($product);
				}
				$em->persist($incomingProduct);
				$savedIncomingProducts[$model]=$incomingProduct;
				$imports++;
			}
		}
		$em->flush();

		if ($imports > 0)
			$this->get('session')->getFlashBag()->add('success', "Created " . $imports . " incoming product(s).");
		else
			$this->get('session')->getFlashBag()->add('error', "No imports were created.");

		return $this->redirectToRoute('incoming_products', ['incoming_id' => $incoming->getId()]);
	}

	/**
	 * Displays a form to create a new Incoming entity.
	 *
	 * @Route("/{id}/import", name="incoming_new_import")
	 * @Method({"GET", "POST"})
	 */
	public function newImportAction(Request $request, Incoming $incoming)
	{
		$incomingFile = (new IncomingFile())->setUser($this->getUser());
		$incomingFile->setIncoming($incoming);
		$form = $this->createForm('WarehouseBundle\Form\IncomingFileType', $incomingFile);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			/** @var UploadedFile $file */
			$file = $incomingFile->getFilepath();
			$fileName = $this->get('app.import_uploader')->upload($file);

			// Update the 'document' property to store the PDF file name
			// instead of its contents
			$incomingFile->setFilepath($fileName);
			$incomingFile->setCreated(new \DateTime("now"));

			# We want to save the modification date
			$incoming->setModified(new \DateTime('now'));

			$em = $this->getDoctrine()->getManager();
			$em->persist($incomingFile);
			$em->persist($incoming);
			$em->flush();

			$editLink = $this->generateUrl('incoming_products',
				['incoming_id' => $incoming->getId()]);
			$this->get('session')
				->getFlashBag()
				->add('success', "<a href='$editLink'>New incoming file was created successfully.</a>");

			return $this->redirectToRoute('incoming_products',
				['incoming_id' => $incoming->getId()]);
		}
		return $this->render('incoming/import.html.twig', [
			'incoming' => $incoming,
			'form' => $form->createView(),
		]);
	}


	/**
	 * Displays a form to edit an existing Incoming entity.
	 *
	 * @Route("/{id}/edit", name="incoming_edit")
	 * @Method({"GET", "POST"})
	 */
	public function editAction(Request $request, Incoming $incoming)
	{
		$deleteForm = $this->createDeleteForm($incoming);
		$editForm = $this->createForm(IncomingType::class, $incoming);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$incoming->setModified(new \DateTime());
			$em->persist($incoming);
			$em->flush();

			$this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
			return $this->redirectToRoute('incoming_edit', ['id' => $incoming->getId()]);
		}
		return $this->render('incoming/edit.html.twig', [
			'incoming' => $incoming,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		]);
	}

	/**
	 * Creates a form to delete a Incoming entity.
	 *
	 * @param Incoming $incoming The Incoming entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(Incoming $incoming)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('incoming_delete', ['id' => $incoming->getId()]))
			->setMethod('DELETE')
			->getForm();
	}

	/**
	 * Deletes a Incoming entity.
	 *
	 * @Route("/{id}", name="incoming_delete")
	 * @Method("DELETE")
	 */
	public function deleteAction(Request $request, Incoming $incoming)
	{

		$form = $this->createDeleteForm($incoming);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();

			# Remove incoming products scans before being able to delete incoming container
			foreach ($incoming->getIncomingScannedProducts() as $scannedProduct) {
				$em->remove($scannedProduct);
			}

			# Remove incoming products before being able to delete incoming container
			foreach ($incoming->getIncomingProducts() as $incomingProduct) {
				$em->remove($incomingProduct);
			}

			# Remove incoming comments before being able to delete incoming container
			foreach ($incoming->getComments() as $comment) {
				$em->remove($comment);
			}

			# Remove incoming files before being able to delete incoming container
			foreach ($incoming->getFiles() as $file) {
				$em->remove($file);
			}

			$em->remove($incoming);
			$em->flush();
			$this->get('session')->getFlashBag()->add('success', 'The Incoming was deleted successfully');
		} else {
			$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Incoming');
		}

		return $this->redirectToRoute('incoming');
	}

	/**
	 * Delete Incoming by id
	 *
	 * @Route("/delete/{id}", name="incoming_by_id_delete")
	 * @Method("GET")
	 */
	public function deleteByIdAction(Incoming $incoming)
	{
		$em = $this->getDoctrine()->getManager();

		try {
			# Remove incoming products scans before being able to delete incoming container
			foreach ($incoming->getIncomingScannedProducts() as $scannedProduct) {
				$em->remove($scannedProduct);
			}

			# Remove incoming products before being able to delete incoming container
			foreach ($incoming->getIncomingProducts() as $incomingProduct) {
				$em->remove($incomingProduct);
			}

			# Remove incoming comments before being able to delete incoming container
			foreach ($incoming->getComments() as $comment) {
				$em->remove($comment);
			}

			# Remove incoming files before being able to delete incoming container
			foreach ($incoming->getFiles() as $file) {
				$em->remove($file);
			}

			$em->remove($incoming);
			$em->flush();
			$this->get('session')->getFlashBag()->add('success', 'The Incoming was deleted successfully');
		} catch (\Exception $ex) {
			$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Incoming');
		}

		return $this->redirect($this->generateUrl('incoming'));

	}


	/**
	 * Bulk Action
	 * @Route("/bulk-action/", name="incoming_bulk_action")
	 * @Method("POST")
	 */
	public function bulkAction(Request $request)
	{
		$ids = $request->get("ids", []);
		$action = $request->get("bulk_action", "delete");

		$cnt_changes = 0;
		if ($action == "delete") {
			try {
				$em = $this->getDoctrine()->getManager();
				$repository = $em->getRepository('WarehouseBundle:Incoming');

				foreach ($ids as $id) {
					$incoming = $repository->find($id);

					# Remove incoming products scans before being able to delete incoming container
					foreach ($incoming->getIncomingScannedProducts() as $scannedProduct) {
						$em->remove($scannedProduct);
					}

					# Remove incoming products before being able to delete incoming container
					foreach ($incoming->getIncomingProducts() as $incomingProduct) {
						$em->remove($incomingProduct);
					}

					# Remove incoming comments before being able to delete incoming container
					foreach ($incoming->getComments() as $comment) {
						$em->remove($comment);
					}

					# Remove incoming files before being able to delete incoming container
					foreach ($incoming->getFiles() as $file) {
						$em->remove($file);
					}

					$em->remove($incoming);
					$em->flush();
					$cnt_changes++;
				}

				$this->get('session')->getFlashBag()->add('success', $cnt_changes . ' incoming containers were deleted successfully!');

			} catch (\Exception $ex) {
				$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the incoming containers ');
			}
		}

		return $this->redirect($this->generateUrl('incoming'));
	}
}
