<?php

namespace Reindesign\XmlImportBundle\Library;


class Import {


    protected $arrConfig;
    protected $arrLogs = [];
    protected $objDatabase = null;


    public function __construct() {

        if ( $this->objDatabase == null ) {

            $this->objDatabase = \Database::getInstance();
        }

        $this->setConfig();
    }


    public function execute() {

        if ( !$this->arrConfig['xmlTable'] ) {

            return null;
        }

        $blnOnlyInsert = false;
        $objXmlImport = new XmlParser();
        $arrIds = $this->getInternalIds();
        $arrEntities = $objXmlImport->parse();

        if ( empty( $arrIds ) ) {

            $blnOnlyInsert = true;
        }

        if ( is_array( $arrEntities ) && !empty( $arrEntities ) ) {

            foreach ( $arrEntities as $arrEntity ) {

                $blnExist = false;

                if ( !$blnOnlyInsert ) {

                    $objEnitity = $this->objDatabase->prepare( "SELECT " . $this->arrConfig['xmlInternalId'] . " FROM ". $this->arrConfig['xmlTable'] ." WHERE " . $this->arrConfig['xmlInternalId'] . " = ?" )->limit(1)->execute( $arrEntity[ $this->arrConfig['xmlInternalId'] ] );
                    $blnExist = $objEnitity->numRows ? true : false;
                }

                if ( !$blnExist ) {

                    $this->arrLogs['inserted'][] = $arrEntity[ $this->arrConfig['xmlInternalId'] ];
                    $this->objDatabase->prepare( "INSERT INTO ". $this->arrConfig['xmlTable'] ." %s" )->set( $arrEntity )->execute();
                }

                else {

                    if ( ( $intIndex = array_search( $arrEntity[ $this->arrConfig['xmlInternalId'] ], $arrIds ) ) !== false ) {

                        unset( $arrIds[ $intIndex ] );
                    }

                    $this->arrLogs['updated'][] = $arrEntity[ $this->arrConfig['xmlInternalId'] ];
                    $this->objDatabase->prepare( "UPDATE ". $this->arrConfig['xmlTable'] ." %s WHERE " . $this->arrConfig['xmlInternalId'] . " = ? " )->set( $arrEntity )->execute( $arrEntity[ $this->arrConfig['xmlInternalId'] ] );
                }
            }
        }

        if ( !empty( $arrIds ) ) {

            $this->arrLogs['deleted'] = $arrIds;
            $strPlaceholder = implode( ',', array_fill( 0, count( $arrIds ), '?' ) );
            $this->objDatabase->prepare( "DELETE FROM ". $this->arrConfig['xmlTable'] ." WHERE " . $this->arrConfig['xmlInternalId'] . " IN (" . $strPlaceholder . ") " )->execute( $arrIds );
        }
    }


    public function getLogs() {

        return $this->arrLogs;
    }


    public function getInternalIds() {

        $arrReturn = [];
        $objInteralIds = $this->objDatabase->prepare( "SELECT " . $this->arrConfig['xmlInternalId'] . " FROM ". $this->arrConfig['xmlTable'] ."" )->execute();

        if ( !$objInteralIds->numRows ) {

            return $arrReturn;
        }

        while ( $objInteralIds->next() ) {

            $arrReturn[] = $objInteralIds->{$this->arrConfig['xmlInternalId']};
        }

        return $arrReturn;
    }


    protected function setConfig() {

        $this->arrConfig = [

            'xmlTable' => null,
            'xmlInternalId' => 'id'
        ];

        foreach ( $this->arrConfig as $strKey => $strValue ) {

            if ( \Config::get( $strKey ) !== null ) {

                $this->arrConfig[ $strKey ] = \Config::get( $strKey );
            }
        }

        $this->arrLogs = [

            'deleted' => [],
            'updated' => [],
            'inserted' => []
        ];
    }
}