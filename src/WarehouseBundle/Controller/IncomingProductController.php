<?php

namespace WarehouseBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\IncomingStatus;
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
use WarehouseBundle\Manager\IncomingManager;
use WarehouseBundle\Manager\IncomingStatusManager;

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
			->setParameter('incoming', $incoming);

		return $this->render('incoming/products.html.twig', [
			'incoming' => $incoming,
			'incomingProducts' => $queryBuilder->getQuery()->getResult(),
		]);
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
		/** @var QueryBuilder $queryBuilder */
		$queryBuilder = $em->getRepository('WarehouseBundle:IncomingProductScan')->createQueryBuilder('ips')
			->where('ips.incoming = :incoming')
			->setParameter('incoming', $incoming);

		# TODO: This should go into a form type as it is duped from ScanController.php
		$form_scan = $this->createModifyScannedForm($incoming);
		$form_scan->handleRequest($request);

		# Submissions will be by ajax

		if ($form_scan->isSubmitted() && $form_scan->isValid()) {
			if (!$request->isXmlHttpRequest()) {
				$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
				return $this->redirect($this->generateUrl('incoming_products_scanned', ['id' => $incoming->getId()]));
			}
		}

		return $this->render('incoming/products_scanned.html.twig', [
			'incoming' => $incoming,
			'incomingProducts' => $queryBuilder->getQuery()->getResult(),
			'form_scan' => $form_scan->createView(),
			'form_new' => $this->createNewScannedForm($incoming)->createView(),
		]);
	}

	/**
	 * Creates a form to modify a Incoming with IncomingProductScan entity.
	 *
	 * @param Incoming $incoming The incoming
	 *
	 * @return Form The form
	 */
	function createModifyScannedForm(Incoming $incoming)
	{
		$form = $this->createFormBuilder($incoming, [
			'csrf_protection' => false,  // <---- set this to false on a per Form Instance basis
		])
			->setAction($this->generateUrl('scan_stock_ajax', ['id' => $incoming->getId()]))
			->setMethod('POST')
			->add('incomingScannedProducts', CollectionType::class, [
				'entry_type' => IncomingProductScanType::class,
				'entry_options' => [
					'attr' => ['class' => 'form-control'],
				],
				'allow_add' => TRUE,
				'allow_delete' => TRUE,
				'prototype' => TRUE,
			]);
		if ($incoming->getStatus()->getId() < IncomingStatus::COMPLETED) {
			$form->add('complete', SubmitType::class, [
				'label' => 'COMPLETE SCAN',
				'attr' => [
					'class' => 'btn btn-dark btn-large',
					'data-confirm' => 'This will mark the container are closed. It will also assign all items to active inventory. Are you sure you are complete?',
				],
			]);
			$form->add('load_data', SubmitType::class, [
				'label' => 'LOAD FROM PACKING LIST',
				'attr' => [
					'class' => 'btn btn-large',
					'data-confirm' => 'Do you want to load packing list data to scanned products?',
				],
			]);
		}
		$form = $form->getForm();
		return $form;
	}

	/**
	 * Creates a form to simply add IncomingProductScan to list.
	 *
	 * @param Incoming $incoming The incoming
	 *
	 * @return Form The form
	 */
	function createNewScannedForm(Incoming $incoming)
	{
		$form = $this->createFormBuilder()
			->setMethod('POST')
			->setAction($this->generateUrl('scan_stock_ajax', ['id' => $incoming->getId()]))
			->add('new', TextType::class, [
				'attr' => [
					'placeholder' => 'Enter SKU',
					'class' => 'form-control col-xs-12',
					'id' => 'enter-sku',
				],
			])
			->add('add', SubmitType::class, [
				'label' => 'Add',
				'attr' => [
					'class' => 'btn btn-success',
				],
			])
			->getForm();
		return $form;
	}

	/**
	 * Submits incoming products scanned form (ajax).
	 *
	 * @Route("/{id}/ajax/products_scanned", name="incoming_products_scanned_ajax")
	 */
	public function incomingProductsScannedAjaxAction(Request $request, Incoming $incoming)
	{
		//todo test this function
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned', ['id' => $incoming->getId()]));
		}

		$em = $this->getDoctrine()->getManager();
		$form_scan = $this->createModifyScannedForm($incoming);
		$form_scan->handleRequest($request);

		# Submissions will be by ajax
		if (!$form_scan->isSubmitted() || !$form_scan->isValid()) {
			$this->get('session')->getFlashBag()->add('error', "Form not valid");
			return $this->redirect($this->generateUrl('incoming_products_scanned', ['id' => $incoming->getId()]));
		}

		// Ensure it isn't already completed.
		if (IncomingManager::isComplete($incoming)) {
			$this->get('session')->getFlashBag()->add('error', "Incoming is already set to complete.");
			return new JsonResponse([], JsonResponse::HTTP_OK);
		}
		try {
			if ($form_scan->get('load_data')->isClicked()) {
				$this->get('warehouse.workflow.incoming_workflow')->loadScannedProducts($incoming);
				$incomingProductScan = $this->get('warehouse.manager.incoming_product_scan_manager')
					->getByIncoming($incoming);
				$incoming->setIncomingScannedProducts($incomingProductScan);
				$form_scan = $this->createModifyScannedForm($incoming);
				$this->get('session')->getFlashBag()
					->add('success', "Packing list pre-loaded.");
			} else {
				$items = $form_scan->getData();
				$em->persist($items);
				$em->flush();
				if ($form_scan->get('complete')->isClicked()) {
					$this->get('warehouse.workflow.incoming_workflow')->setIncomingComplete($incoming);
					$this->get('session')->getFlashBag()->add('success', "Incoming container scanned list was saved and Incoming container is now complete.");
				}
			}
			$response = [];
			$response['ajaxCommand'][] = [
				'selector' => '#scanned_form_wrap',
				'op' => 'html',
				'value' => $this->renderView('incoming/products_scanned_form.html.twig', [
					'form_scan' => $form_scan->createView(),
					'form_new' => $this->createNewScannedForm($incoming)->createView(),
					'incoming' => $incoming,
				]),
			];
			return new JsonResponse($response, 200);
		} catch (\Exception $exception) {
			$msg = "An error occurred. " . $exception->getMessage();
			$this->get('session')->getFlashBag()->add('error', $msg);
			return $this->redirect($this->generateUrl('incoming_products_scanned', ['id' => $incoming->getId()]));
		}
	}

	/**
	 * Lists all Incoming entity products.
	 *
	 * @Route("/{id}/ajax/products_scanned_new", name="incoming_products_scanned_new_ajax")
	 */
	public function incomingProductsScannedNewAjaxAction(Request $request, Incoming $incoming)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned', ['id' => $incoming->getId()]));
		}

		$em = $this->getDoctrine()->getManager();
		$form_new = $this->createNewScannedForm($incoming);
		$form_new->handleRequest($request);

		# Submissions will be by ajax
		if ($form_new->isSubmitted() && $form_new->isValid()) {
			$model = trim($request->request->get('form')['new']);

			# Check if it exists?
			$incomingProduct = $em->getRepository('WarehouseBundle:IncomingProduct')->findOneByModel($incoming, $model);
			$item = $em->getRepository('WarehouseBundle:IncomingProductScan')->findOneByModel($incoming, $model, FALSE); # Non assigned only
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
					$this->get('session')->getFlashBag()->add('warning', "<strong>" . $model . "</strong> was created as a new product.");
				}
				$item = (new IncomingProductScan())
					->setIncoming($incoming)
					->setIncomingProduct($incomingProduct)
					->setQtyOnScan(1)
					->setProduct($product)
					->setCreated(new \DateTime('now'));

				if (!$incomingProduct)
					$this->get('session')->getFlashBag()->add('success', "<strong>" . $model . "</strong> was not identified in the Incoming container however it was added to this list.");
				else
					$this->get('session')->getFlashBag()->add('success', "Successfully added <strong>$model</strong>.");
			} else {
				# Update the scan item
				$item->setModified(new \DateTime('now'));
				$item->setQtyOnScan($item->getQtyOnScan() + 1);
				$this->get('session')->getFlashBag()->add('success', "Increased unassigned quantity to <strong>$model</strong>.");
			}
			$item->setUser($this->getUser());
			$em->persist($item);
			$em->flush();
			$formView = $this->createModifyScannedForm($incoming)->createView();

			$response['ajaxCommand'][] = [
				'selector' => '#scanned_form_wrap',
				'op' => 'html',
				'value' => $this->renderView('incoming/products_scanned_form.html.twig', [
					'form_scan' => $formView,
					'form_new' => $form_new->createView(),
					'incoming' => $incoming,
				]),
			];

			return new JsonResponse($response, 200);
		}
	}

	/**
	 * Ajax deletion of an IncomingProductScan item.
	 *
	 * @Route("/incoming-delete/ajax/{id}", name="incoming_products_scanned_delete_ajax")
	 */
	public function incomingProductsScannedDeleteAjaxAction(Request $request, IncomingProductScan $incomingProductScan)
	{
		$em = $this->getDoctrine()->getManager();
		$response = [];
		$incoming = $incomingProductScan->getIncoming();
		if (IncomingStatusManager::haveStatus($incoming, [IncomingStatus::INBOUND, IncomingStatus::ARRIVED])) {
			$em->remove($incomingProductScan);
			$em->flush();
		} else {
			$this->get('session')->getFlashBag()->add('error', "Incoming container is no longer in active/arrived status.");
		}

		$response['ajaxCommand'][] = [
			'selector' => '.loading',
			'op' => 'hide',
			'value' => '',
		];
		$response['ajaxCommand'][] = [
			'selector' => '#scanned_form_wrap',
			'op' => 'html',
			'value' => $this->renderView('incoming/products_scanned_form.html.twig', [
				'form_scan' => $this->createModifyScannedForm($incoming)->createView(),
				'form_new' => $this->createNewScannedForm($incoming)->createView(),
				'incoming' => $incoming,
			]),
		];
		return new JsonResponse($response, 200);
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
		$form = $this->createForm('WarehouseBundle\Form\IncomingProductType', $incomingProduct);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$incomingProduct->setCreated(new \DateTime('now'));
			$em->persist($incomingProduct);
			$em->flush();

			$editLink = $this->generateUrl('incoming_product_edit', ['id' => $incomingProduct->getId()]);
			$this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New incoming was created successfully.</a>");

			if ($request->get('submit') == 'save')
				return $this->redirectToRoute('incoming_products', ['incoming_id' => $incoming->getId()]);
			else
				return $this->redirectToRoute('incoming_product_new', ['incoming' => $incoming->getId()]);
		}
		return $this->render('incoming/product/new.html.twig', [
			'incoming' => $incoming,
			'form' => $form->createView(),
		]);
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
			return $this->redirectToRoute('incoming_product_edit', ['id' => $incomingProduct->getId()]);
		}
		return $this->render('incoming/product/edit.html.twig', [
			'incomingProduct' => $incomingProduct,
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
	private function createDeleteForm(IncomingProduct $incomingProduct)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('incoming_product_delete', ['incoming' => $incomingProduct->getIncoming()->getId(), 'id' => $incomingProduct->getId()]))
			->setMethod('DELETE')
			->getForm();
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

		return $this->redirectToRoute('incoming_products', ['incoming_id' => $incoming->getId()]);
	}

	/**
	 * Delete Incoming by id
	 *
	 * @Route("/{incoming}/delete/{id}", name="incoming_product_by_id_delete")
	 * @Method("GET")
	 */
	public function deleteByIdAction(Incoming $incoming, IncomingProduct $incomingProduct)
	{
		$em = $this->getDoctrine()->getManager();

		try {
			$em->remove($incomingProduct);
			$em->flush();
			$this->get('session')->getFlashBag()->add('success', 'The Incoming Product was deleted successfully');
		} catch (\Exception $ex) {
			$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Incoming Product');
		}

		return $this->redirect($this->generateUrl('incoming_products', ['incoming_id' => $incoming->getId()]));

	}


	/**
	 * Bulk Action
	 * @Route("/{incoming}/bulk-action", name="incoming_product_bulk_action")
	 * @Method("POST")
	 */
	public function bulkAction(Request $request, Incoming $incoming)
	{
		$ids = $request->get("ids", []);
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

				$this->get('session')->getFlashBag()->add('success', $cnt_changes . ' incoming products were deleted successfully!');

			} catch (\Exception $ex) {
				$this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the incoming products ');
			}
		}

		return $this->redirect($this->generateUrl('incoming_products', ['incoming_id' => $incoming->getId()]));
	}

}
