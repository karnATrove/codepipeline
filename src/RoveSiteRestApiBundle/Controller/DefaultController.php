<?php

namespace RoveSiteRestApiBundle\Controller;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Rove\CanonicalDto\Order\OrderCommentCreateDto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/rove-site-rest-api")
 */
class DefaultController extends Controller
{
	/**
	 * @Route("/test")
	 */
	public function indexAction()
	{
		return $this->render('RoveSiteRestApiBundle:Default:index.html.twig');
	}
}
