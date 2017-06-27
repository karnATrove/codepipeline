<?php

namespace WarehouseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WarehouseBundle\Entity\Product;
use WarehouseBundle\Entity\Location;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @Route("/product", name="product")
     * @Method("GET")
     *
     * @param Request $request Request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('WarehouseBundle:Product')->createQueryBuilder('e');
        
        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($products, $pagerHtml) = $this->paginator($queryBuilder, $request);
        
        return $this->render('WarehouseBundle::Product/index.html.twig', array(
            'products' => $products,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        ));
    }


    /**
     * @Route("/product/create", name="product_create")
     *
     * @param Request $request Request
     *
     * @return Response
     */
    public function createAction(Request $request) {
        
        $product = new Product();
        $product->setModel('E1-001');
        $product->setDescription('Wozers');
        $product->setQtyPerCarton(1);
        $product->setOnHold(0);
        $product->setLength(NULL);
        $product->setWidth(NULL);
        $product->setHeight(NULL);
        $product->setDimUnits('in');
        $product->setweight(NULL);
        $product->setWeightUnits('lbs');
        $product->setCreated(new \DateTime("now"));

        $location = new Location();
        $location->setAisle('A');
        $location->setRow(3);
        $location->setLevel(2);
        $location->setOnHand(10);
        $location->setCreated(new \DateTime("now"));
        $location->setModified(NULL);
        $location->setProduct($product);

        $em = $this->getDoctrine()->getManager();
        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($product);
        $em->persist($location);
       
        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('Saved new product with id '.$product->getId().' and new location with id '. $location->getId());
    }

    /**
     * @Route("/product/update/{productId}", name="product_update")
     *
     * @param Request $request Request
     * @param string  $productId
     *
     * @return Response
     */
    public function updateAction(Request $request, $productId) {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('WarehouseBundle:Product')->find($productId);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '. $productId
            );
        }

        // remove one location
        

        // Add new location
        $location = new Location();
        $location->setAisle('B');
        $location->setRow(7);
        $location->setLevel(2);
        $location->setOnHand(20);
        $location->setCreated(new \DateTime("now"));
        $location->setModified(NULL);
        $location->setProduct($product);

        $product->setDescription('Test change');
        $em->persist($product);
        $em->persist($location);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/product/deletex/{productId}", name="product_delete")
     *
     * @param Request $request Request
     * @param string  $productId
     *
     * @return Response
     */
    public function deleteAction(Request $request, $productId) {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('WarehouseBundle:Product')->find($productId);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '. $productId
            );
        }
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/product/show/{productId}", name="product_show")
     *
     * @param Request $request Request
     * @param string  $productId
     *
     * @return Response
     */
    public function showAction(Request $request, $productId) {
        $product = $this->getDoctrine()
            ->getRepository('WarehouseBundle:Product')
            ->find($productId);

        exit(\Doctrine\Common\Util\Debug::dump($product->getLocations()));
        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '. $productId
            );
        }

        return $this->render('app/pages/dashboard.html.twig', []);
    }
}
