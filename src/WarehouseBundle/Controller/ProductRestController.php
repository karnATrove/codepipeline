<?php
namespace WarehouseBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Validator\ConstraintViolationList;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\ProductLog;

/**
 * Rest Product controller.
 * @package WarehouseBundle\Controller
 * @author Brendan Burscough <brendan@roveconcepts.com>
 */
class ProductRestController extends Controller
{
    /**
     * Return the overall model list.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Return the overall Model List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getModelsAction()
    {
        $productManager = $this->get('ProductManager');
        $products = $productManager->findModels();
        if (!$products) {
            throw $this->createNotFoundException('Products not found.');
        }
        $view = View::create();
        $view->setData($products)->setStatusCode(200);
        return $view;
    }

    /**
     * Return the overall product list.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Return the overall Product List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the product is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getProductsAction()
    {
        $productManager = $this->get('ProductManager');
        $products = $productManager->findProducts();
        if (!$products) {
            throw $this->createNotFoundException('Products not found.');
        }
        $view = View::create();
        $view->setData($products)->setStatusCode(200);
        return $view;
    }

    /**
     * Return an product identified by unique id.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Return an product identified by unique id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the product is not found"
     *   }
     * )
     *
     * @Annotations\View(
     *     template = "WarehouseBundle:Product:getProduct.html.twig",
     *     templateVar="product"
     * )
     * 
     * @param integer $id product id
     *
     * @return View
     */
    public function getProductAction(Product $product)
    {
        if (false === $product) {
            throw $this->createNotFoundException("Product does not exist.");
        }
        return $product;
    }

    /**
     * Create a Product from the submitted form data.<br/>
     *
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Creates a new product from the submitted form data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   input="WarehouseBundle\Form\ProductRestType"
     * )
     *
     * @param Request $request Request
     *
     * @return View
     */
    public function postProductFormAction(Request $request)
    { 
        $productManager = $this->get('ProductManager');
        $product = ($productManager->createProduct())->setCreated(new \DateTime('now'));
        //$form = $this->container->get('fos_comment.form_factory.thread')->createForm();
        $form = $this->createForm(new ProductRestType(),$product,array('method'=>'POST'));
        $form->setData($product);
        $form->handleRequest($request);

        if (!$form->isValid()) {
          throw new \Symfony\Component\HttpKernel\Exception\HttpException(Response::HTTP_BAD_REQUEST, $form->getErrors(true, false));
        }

        if ($form->isValid()) {
            $productManager->updateProduct($product);
            return View::create($product, 200);
            return $product;
        }
        return View::create($form, 400);
        return $form;
        
        $view = View::create();
        $errors = $this->get('validator')->validate($product);
        if (count($errors) == 0) {
            //$productManager->updateProduct($product,TRUE); #flush
            $view->setData($product)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Create a Product from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Creates a new product from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="model", nullable=false, strict=true, description="Model.")
     * @RequestParam(name="description", nullable=false, strict=true, description="Description.")
     * @RequestParam(name="qtypercarton", nullable=false, strict=false, default="1", description="Quantity Per Carton.")
     * @RequestParam(name="length", nullable=false, strict=true, description="The length.")
     * @RequestParam(name="width", nullable=false, strict=true, description="The width.")
     * @RequestParam(name="height", nullable=false, strict=true, description="The height.")
     * @RequestParam(name="dim_units", nullable=false, strict=false, default="in", description="The units for dimensions.")
     * @RequestParam(name="weight", nullable=false, strict=true, description="The weight.")
     * @RequestParam(name="weight_units", nullable=false, strict=false, default="lbs", description="The units for weight.")
     * @RequestParam(name="status", nullable=false, strict=false, default="1", description="The Status.")
     *
     * @return View
     */
    public function postProductAction(ParamFetcher $paramFetcher)
    {
       // return $this->createForm(new ProductRestType());
        $manipulator = $this->get('app.product');
        $product = $manipulator->create(
            $paramFetcher->get('model'),
            $paramFetcher->get('description'),
            $paramFetcher->get('qtypercarton'),
            $paramFetcher->get('length'),
            $paramFetcher->get('width'),
            $paramFetcher->get('height'),
            $paramFetcher->get('dim_units'),
            $paramFetcher->get('weight'),
            $paramFetcher->get('weight_units'),
            $paramFetcher->get('status')
        );

        /*
        if (is_array($paramFetcher->get('locations'))) {
            $this->get('session')->getFlashBag()->add('success',  'array: '. print_r($paramFetcher->get('products'),TRUE));
        } elseif (is_object($paramFetcher->get('locations'))) {
            $this->get('session')->getFlashBag()->add('success',  'object: '. print_r($paramFetcher->get('products'),TRUE));
        } else {
            $this->get('session')->getFlashBag()->add('success',  'string: '. $paramFetcher->get('locations'));
        }
        */

        $view = View::create();
        $errors = $this->get('validator')->validate($product);
        if (count($errors) == 0) {
            //$productManager->updateProduct($product,TRUE); #flush
            $view->setData($product)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Update a Product from the submitted data by ID.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Updates a product from the submitted data by ID.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="Unique ID.")
     * @RequestParam(name="ordernumber", nullable=true, strict=true, description="Order number from client.")
     * @RequestParam(name="orderreference", nullable=true, strict=true, description="Order reference from client.")
     * @RequestParam(name="ordertype", nullable=true, strict=true, description="Order type.")
     * @RequestParam(name="carrierid", nullable=true, strict=true, description="The carrier.")
     *
     * @return View
     */
    public function putProductAction(ParamFetcher $paramFetcher)
    {
        $productManager = $this->get('ProductManager');
        $product = $productManager->findProductBy(
            array('id' => $paramFetcher->get('id'))
        );

        if (false === $product) {
            throw $this->createNotFoundException("Product does not exist.");
        }
        // product dont have ordernumber/orderreference/ordertype/carrierid
//        if($paramFetcher->get('ordernumber')){$product->setOrderNumber($paramFetcher->get('ordernumber'));}
//        if($paramFetcher->get('orderreference')){$product->setOrderReference($paramFetcher->get('orderreference'));}
//        if($paramFetcher->get('ordertype')){$product->setOrderType($paramFetcher->get('ordertype'));}
//        if($paramFetcher->get('carrierid')){$product->setCarrierId($paramFetcher->get('carrierid'));}
        $product->setModified(new \DateTime('now'));

        $view = View::create();
        $errors = $this->get('validator')->validate($product);
        if (count($errors) == 0) {
            $productManager->updateProduct($product,TRUE); # Flush
            $view->setData($product)->setStatusCode(200);
            return $view;
        } else {
            $view = $this->getErrorsView($errors);
            return $view;
        }
    }

    /**
     * Delete an product identified by unique id.
     *
     * @Secure(roles="ROLE_API")
     * @ApiDoc(
     *   resource = "Product",
     *   description = "Delete an product identified by unique id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the product is not found"
     *   }
     * )
     *
     * @param string $id unique id
     *
     * @return View
     */
    public function deleteProductAction($id)
    {
        $productManager = $this->get('ProductManager');
        $product = $productManager->findProductBy(
            array('id' => $id)
        );

        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        $productManager->deleteProduct($product);

        $view = View::create();
        $view->setData("Product deleted.")->setStatusCode(204);
        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);
        return $view;
    }

}
