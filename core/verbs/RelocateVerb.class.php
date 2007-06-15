<?php
/**
 * Relocate file
 * FIXME Colocar o parametro xfa no relocate, se tiver os dois a url prevalece
 */
class RelocateVerb extends AbstractVerb {
    
    
    private $url;
    
    public function getUrl() {
        return $this->url;
    }
    
    public function setUrl( $url ) {
        $this->url = $url;
    }

    public function getData() {
        $data = parent::getData();
        $data[ "attributes" ][ "url" ] = $this->getUrl();
        return $data;
    }
    
    public function setData( $data ) {
        parent::setData( $data );
        $this->setUrl( $data[ "attributes" ][ "url" ] );
    }
    

    /**
     * Return the parsed code
     *
     * @return string
     */
    public function getParsedCode( $commented, $identLevel ) {
        $strOut = parent::getParsedCode( $commented, $identLevel );
        $strOut .= str_repeat( "\t", $identLevel );
        $controllerClass = $this->getAction()->getCircuit()->
	        getApplication()->getControllerClass();
        $strOut .=  $controllerClass . "::sendToUrl( \"" . 
            $this->getUrl() . "\" );\n\n";
        return $strOut;
    }

    /**
     * Return the parsed comments
     *
     * @return string
     */
    public function getComments( $identLevel ) {
        $strOut = parent::getComments( $identLevel );
        $strOut = str_replace( "__COMMENT__",
            "MyFuses:request:action:relocate url=\"" . 
            $this->getUrl() . "\"", $strOut );
        return $strOut;
    }

}