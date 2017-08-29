<?php

namespace WarehouseBundle\Workflow\ControllerWorkflow;

use Symfony\Component\HttpFoundation\Request;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Manager\ProductManager;
use WarehouseBundle\Model\Product\ProductSearchModel;

class ProductControllerWorkflow extends BaseControllerWorkflow
{
	public function indexAction(Request $request)
	{
		$keyword = $request->get('keyword', null);
		$numberPerPage = $request->get('numberPerPage', 25);
		$productWithQtyOnly = $keyword != null ? false : $request->get('productWithQtyOnly', true);
		$status = $request->get('status', Product::PRODUCT_STATUS_ACTIVE);

		$searchModel = (new ProductSearchModel())
			->setSearchString($keyword)
			->setProductHaveQuantityOnly($productWithQtyOnly)
			->setStatus($status);

		$query = $this->container->get(ProductManager::class)->searchProducts($searchModel, true);
		$paginator = $this->container->get('knp_paginator');
		$pagination = $paginator->paginate(
			$query,
			$request->query->getInt('page', 1),
			$numberPerPage
		);

		return $this->templating->renderResponse('WarehouseBundle::Product/index.html.twig', [
			'pagination' => $pagination,
			'keyword' => $keyword,
			'productWithQtyOnly' => $productWithQtyOnly,
			'numberPerPage' => $numberPerPage
		]);
	}
}