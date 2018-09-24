<?php

namespace Reindesign\XmlImportBundle\Library;


class Import {


    protected $objDatabase = null;


    public function __construct() {

        if ( $this->objDatabase == null ) {

            $this->objDatabase = \Database::getInstance();
        }
    }


    public function execute() {

        $objXmlImport = new XmlParser();
        $arrEntities = $objXmlImport->parse();

        // var_dump( $arrEntities ); exit;
    }
}