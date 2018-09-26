<?php

namespace Reindesign\XmlImportBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 *
 * @Route("/xml-import", defaults={"_scope" = "frontend", "_token_check" = false})
 */
class BaseController extends Controller {


    /**
     *
     * @Route("/{alias}", name="execute")
     * @Method({"GET"})
     */
    public function execute( $alias ) {

        $this->container->get( 'contao.framework' )->initialize();

        if ( \Config::get('xmlAccess') !== $alias ) {

            throw new \CoreBundle\Exception\PageNotFoundException( 'Page not found: ' . \Environment::get('uri') );
        }

        $objImport = new \Reindesign\XmlImportBundle\Library\Import();
        $objImport->execute();

        header('Content-Type: application/json');
        echo json_encode( $objImport->getLogs(), 512 );
        exit;
    }
}