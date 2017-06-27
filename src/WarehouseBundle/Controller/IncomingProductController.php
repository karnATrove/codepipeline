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
use WarehouseBundle\Manager\LocationManager;

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

		return $this->render('WarehouseBundle::Incoming/products.html.twig', [
			'incoming' => $incoming,
			'incomingProducts' => $queryBuilder->getQuery()->getResult(),
		]);
	}

	/**
	 * Creates a form to simply add IncomingProductScan to list.
	 *
	 * @param Incoming $incoming The incoming
	 *
	 * @return Form The form
	 */
	public function createNewScannedForm(Incoming $incoming)
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
		return $this->render('WarehouseBundle::Incoming/product/new.html.twig', [
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
		return $this->render('WarehouseBundle::Incoming/product/edit.html.twig', [
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
