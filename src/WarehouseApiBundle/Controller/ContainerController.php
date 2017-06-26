<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-06-23
 * Time: 1:50 PM
 */

namespace WarehouseApiBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\Serializer\SerializerBuilder;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rove\CanonicalDto\Container\ContainerDto;
use Rove\CanonicalDto\Response\ResponseErrorDto;
use RoveSiteRestApiBundle\Manager\ContainerManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContainerController extends FOSRestController
{
	/**
	 * Create new container
	 *
	 * @Secure(roles="ROLE_API")
	 * @ApiDoc(
	 *   resource = "Container",
	 *   description = "Create new container",
	 *   statusCodes = {
	 *     201 = "Created",
	 *     400 = "Failed",
	 *     500 = "Error"
	 *   }
	 * )
	 *
	 * @return View
	 */
	public function postContainersAction(Request $request)
	{
		try {
			$json = $request->getContent();
			$serializer = SerializerBuilder::create()->build();
			/** @var ContainerDto $containerDto */
			$containerDto = $serializer->deserialize($json, ContainerDto::class, 'json');
//			$containerManager = $this->get('container')


//		$productApiManager = new ProductManager($this->get('service_container'));
//		$productDto = $productApiManager->get('sku');
//			$view = View::create();
//			return $view;
		} catch (\Exception $exception) {
			$errorDto = new ResponseErrorDto(Response::HTTP_INTERNAL_SERVER_ERROR, "Error", $exception->getMessage());
			$view = View::create();
			$view->setData($errorDto)->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			return $view;
		}
	}
}