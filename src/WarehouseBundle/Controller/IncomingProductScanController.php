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

use Symfony\Component\Security\Acl\Exception\Exception;
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
 * @Route("/incoming_product_scan")
 */
class IncomingProductScanController extends Controller
{
	/**
	 * Lists all Incoming entity products.
	 *
	 * @Route("/{id}/ajax/edit", name="incoming_product_scan_edit_ajax")
	 */
	public function incomingProductScanEditAjaxAction(Request $request, IncomingProductScan $incomingProductScan)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->get('session')->getFlashBag()->add('error', "Form should have been submitted via AJAX.");
			return $this->redirect($this->generateUrl('incoming_products_scanned', array('id' => $incomingProductScan->getIncoming())));
		}

		$qty = $request->get('quantity');
		$locationId = $request->get('location');
		if ($qty) {
			$incomingProductScan->setQtyOnScan($qty);
		}
		$em = $this->getDoctrine()->getManager();
		if ($locationId) {
			$location = $em->getRepository('WarehouseBundle:Location')->find($locationId);
			if (!$location) {
				throw new Exception('Location not found');
			}
			$incomingProductScan->setLocation($location);
		}

		//persist data
		$em->persist($incomingProductScan);
		$em->flush();

		$locationTitle = $incomingProductScan->getLocation() ? $incomingProductScan->getLocation()->printLocation() : "NULL";
		$message = "Model {$incomingProductScan->getProduct()->getModel()} updated with location: " .
			$locationTitle . " Quantity: {$incomingProductScan->getQtyOnScan()}";
		$response = ['status' => 'success', 'error' => "", 'message' => $message];
		return new JsonResponse($response, 200);
	}

}