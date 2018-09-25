<?php

namespace Reindesign\XmlImportBundle\Library;


class Import {


    protected $strTable;
    protected $objDatabase = null;


    public function __construct() {

        if ( $this->objDatabase == null ) {

            $this->objDatabase = \Database::getInstance();
        }

        $this->strTable = \Config::get( 'xmlTable' );
    }


    public function execute() {

        if ( !$this->strTable ) {

            return null;
        }

        $objXmlImport = new XmlParser();
        $arrEntities = $objXmlImport->parse();

        if ( is_array( $arrEntities ) && !empty( $arrEntities ) ) {

            foreach ( $arrEntities as $arrEntity ) {

                // $this->objDatabase->prepare( "INSERT INTO ". $this->strTable ." %s" )->set( $arrEntity )->execute();
            }
        }
    }
}