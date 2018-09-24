<?php

namespace Reindesign\XmlImportBundle\Library;


class XmlParser {


    protected $strDate;
    protected $strSource;
    protected $arrMap = [];
    protected $arrDateTypes = [];
    protected $arrGeoFields = [];
    protected $blnGeoCoding = false;
    protected $arrGeoDestination = [];

    public function __construct() {

        $this->setMap();
        $this->strDate = \Config::get( 'dateFormat' );
        $this->strSource = \Config::get( 'xmlSource' );
        $this->strKeyname = \Config::get( 'xmlKeyname' );
        $this->arrDateTypes = \Config::get( 'xmlDateFields' );

        $this->arrGeoFields = \Config::get( 'xmlGeoFields' );
        $this->blnGeoCoding = \Config::get( 'xmlUseGeoCoding' );
        $this->arrGeoDestination = \Config::get( 'xmlGeoDestination' );

        if ( \Config::get( 'xmlDateFormat' ) ) {

            $this->strDate = \Config::get( 'xmlDateFormat' );
        }
    }


    protected function read() {

        if ( substr( $this->strSource, 0, 4 ) === 'http' ) {

            $objFile = new XmlFile( $this->strSource );
        }

        else {

            $objFile = new \File( $this->strSource, true );

            if ( !$objFile->exists() ) {

                \System::log( 'File to import ' . $this->strSource . ' doesn\'t exist.', __METHOD__, TL_ERROR );
            }

            if ( $objFile->extension !== 'xml' ) {

                \System::log( 'File ' . $this->strSource . ' is not an XML file.', __METHOD__, TL_ERROR );
            }
        }

        return $objFile;
    }


    protected function setMap() {

        $arrMap = \Config::get( 'xmlMap' );

        if ( is_array( $arrMap ) && !empty( $arrMap ) ) {

            foreach ( $arrMap as $strXmlField => $strDbName ) {

                $strDbName = $strDbName == '' ? $strXmlField : $strDbName;

                $this->arrMap[ $strXmlField ] = $strDbName;
            }
        }
    }


    public function parse() {

        $objFile = $this->read();
        $objXml = new \SimpleXMLElement( $objFile->getContent() );

        if ( !$objXml ) {

            \System::log( 'XML can not be parsed.', __METHOD__, TL_ERROR );
        }

        $arrEntities = [];

        foreach ( $objXml->{$this->strKeyname} as $objEntity ) {

            $arrData = [];
            $arrEntity = (array) $objEntity;

            foreach ( $arrEntity as $strFieldname => $strValue ) {

                $strXmlName = \StringUtil::generateAlias( $strFieldname );
                $strFieldname = isset( $this->arrMap[ $strXmlName ] ) ? $this->arrMap[ $strXmlName ] : $strXmlName;

                $arrData[ $strFieldname ] = $this->parseValue( $strValue, $strFieldname );

                if ( $this->blnGeoCoding ) {

                    //
                }
            }

            $arrEntities[] = $arrData;
        }

        return $arrEntities;
    }


    protected function parseValue( $strValue, $strFieldname ) {

        $strValue = (string) $strValue;

        if ( in_array( $strFieldname, $this->arrDateTypes ) ) {

            $objDate = new \Date( $strValue, $this->strDate );

            return $objDate->tstamp;
        }

        return $strValue;
    }
}