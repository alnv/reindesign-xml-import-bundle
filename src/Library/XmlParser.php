<?php

namespace Reindesign\XmlImportBundle\Library;


class XmlParser {


    protected $arrConfig;
    protected $arrMap = [];


    public function __construct() {

        $this->setMap();
        $this->setConfig();
    }


    protected function setConfig() {

        $this->arrConfig = [

            'xmlSource' => null,
            'xmlKeyname' => null,
            'xmlGeoFields' => [],
            'xmlDateFields' => [],
            'xmlGeoDestination' => '',
            'xmlUseGeoCoding' => false,
            'xmlDateFormat' => \Config::get( 'dateFormat' )
        ];

        foreach ( $this->arrConfig as $strKey => $strValue ) {

            if ( \Config::get( $strKey ) !== null ) {

                $this->arrConfig[ $strKey ] = \Config::get( $strKey );
            }
        }
    }


    protected function read() {

        if ( substr( $this->arrConfig['xmlSource'], 0, 4 ) === 'http' ) {

            $objFile = new XmlFile( $this->arrConfig['xmlSource'] );
        }

        else {

            $objFile = new \File( $this->arrConfig['xmlSource'], true );

            if ( !$objFile->exists() ) {

                \System::log( 'File to import ' . $this->arrConfig['xmlSource'] . ' doesn\'t exist.', __METHOD__, TL_ERROR );
            }

            if ( $objFile->extension !== 'xml' ) {

                \System::log( 'File ' . $this->arrConfig['xmlSource'] . ' is not an XML file.', __METHOD__, TL_ERROR );
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

        foreach ( $objXml->{$this->arrConfig['xmlKeyname']} as $objEntity ) {

            $arrData = [];
            $arrEntity = (array) $objEntity;

            foreach ( $arrEntity as $strFieldname => $strValue ) {

                $strXmlName = \StringUtil::generateAlias( $strFieldname );
                $strFieldname = isset( $this->arrMap[ $strXmlName ] ) ? $this->arrMap[ $strXmlName ] : $strXmlName;

                $arrData[ $strFieldname ] = $this->parseValue( $strValue, $strFieldname );
            }

            if ( $this->arrConfig['xmlUseGeoCoding'] ) {

                $arrAddress = [];

                foreach ( $this->arrConfig['xmlGeoFields'] as $strField ) {

                    if ( $arrData[ $strField ] ) {

                        $arrAddress[] = $arrData[ $strField ];
                    }
                }

                if ( !empty( $arrAddress ) ) {

                    $objGeoCoding = new \Contao\GeoCoding\Library\GeoCoding();
                    $arrResults = $objGeoCoding->getGeoCodingByAddress( implode( ',', $arrAddress ), 'de' );
                    $arrData[ $this->arrConfig['xmlGeoDestination'] ] = $arrResults['latitude'] . ',' . $arrResults['longitude']; // @todo allow to seperate values
                }
            }

            $arrEntities[] = $arrData;
        }

        return $arrEntities;
    }


    protected function parseValue( $strValue, $strFieldname ) {

        $strValue = (string) $strValue;

        if ( in_array( $strFieldname, $this->arrConfig['xmlDateFields'] ) ) {

            $objDate = new \Date( $strValue, $this->arrConfig['xmlDateFormat'] );

            return $objDate->tstamp;
        }

        return $strValue;
    }
}