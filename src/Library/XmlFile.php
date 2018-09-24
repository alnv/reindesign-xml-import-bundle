<?php

namespace Reindesign\XmlImportBundle\Library;


class XmlFile {


    protected $strFile;


    public function __construct( $strFile ) {

        $this->strFile = $strFile;
    }


    public function getContent() {

        return file_get_contents( $this->strFile );
    }
}