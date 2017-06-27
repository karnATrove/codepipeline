<?php

namespace WarehouseBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use WarehouseBundle\Entity\Booking;
use WarehouseBundle\Entity\Product;

/**
 * Product controller.
 *
 * @Route("/log")
 */
class LogController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @Route("/product/{id}", name="log_product")
     * @Method("GET")
     */
    public function productAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('WarehouseBundle:ProductLog')
            ->createQueryBuilder('pl')
            ->where('pl.product = :product')
            ->setParameter('product', $product)
            ->orderBy('pl.created', 'DESC');

        //list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($log_entries, $pagerHtml) = $this->paginator($queryBuilder, $request, $product);

        return $this->render('WarehouseBundle::Log/product.html.twig', array(
            'product' => $product,
            'log_entries' => $log_entries,
            'pagerHtml' => $pagerHtml,
        ));
    }

    /**
     * Get results from paginator and get paginator view.
     *
     */
    protected function paginator(QueryBuilder $queryBuilder, Request $request, Product $product)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show', 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }

        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function ($page) use ($me, $request, $product) {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            $requestParams['id'] = $product->getId();
            return $me->generateUrl('log_product', $requestParams);
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
     * Lists all Product entities.
     *
     * @Route("/booking/{id}", name="log_booking")
     * @Method("GET")
     */
    public function bookingAction(Request $request, Booking $booking)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('WarehouseBundle:BookingLog')
            ->createQueryBuilder('bl')
            ->where('bl.booking = :booking')
            ->setParameter('booking', $booking)
            ->orderBy('bl.created', 'DESC');

        //list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($log_entries, $pagerHtml) = $this->bookingLogPaginator($queryBuilder, $request, $booking);

        return $this->render('WarehouseBundle::Log/booking.html.twig', array(
            'booking' => $booking,
            'log_entries' => $log_entries,
            'pagerHtml' => $pagerHtml,
        ));
    }


    protected function bookingLogPaginator(QueryBuilder $queryBuilder, Request $request, Booking $booking)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show', 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }

        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function ($page) use ($me, $request, $booking) {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            $requestParams['id'] = $booking->getId();
            return $me->generateUrl('log_booking', $requestParams);
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
}