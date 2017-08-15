<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            //new AppBundle\AppBundle(),
            new WarehouseBundle\WarehouseBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(), # Asset Management
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(), # PDF Generator
            new BG\BarcodeBundle\BarcodeBundle(), # BarCode Generator
            new Liuggio\ExcelBundle\LiuggioExcelBundle(), # Excel reader
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(), # Doctine extensions such as logging...

            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SerializerBundle\JMSSerializerBundle(),

            new FOS\RestBundle\FOSRestBundle(),
            new FOS\HttpCacheBundle\FOSHttpCacheBundle(), # HTTP Caching
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(), # Documentation
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(), # Hateoas RESTful API
            new Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle(), # Extended routing bundle
            new Bazinga\Bundle\RestExtraBundle\BazingaRestExtraBundle(), # Extra REST bundle

            new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
            new Petkopara\MultiSearchBundle\PetkoparaMultiSearchBundle(),
            new Petkopara\CrudGeneratorBundle\PetkoparaCrudGeneratorBundle(),
            new ReportBundle\ReportBundle(),
            new RoveSiteRestApiBundle\RoveSiteRestApiBundle(),
	        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new WarehouseApiBundle\WarehouseApiBundle(),

	        //rove
	        new Rove\RoveSiteRestApiBundle\RoveRoveSiteRestApiBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
