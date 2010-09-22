<?php
/**
 * MyFusesPagingHandler - MyFusesPagingHandler.class.php
 *
 * Handles Pagination
 * 
 * PHP version 5
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the Lgroupicense is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * The Original Code is Iflux Paging Handler class part of Iflux Framework .
 * 
 * The Initial Developer of the Original Code is Flávio Gonçalves Garcia.
 * Portions created by Flavio Goncalves Garcia are Copyright (C) 2006 - 2009.
 * Portions created by Luiz Fernando Siroteau Serique Júnior are Copyright 
 * (C) 2006 - 2009.
 * All Rights Reserved.
 * 
 * Contributor(s): Flavio Goncalves Garcia.
 *                 Luiz Fernando Siroteau Serique Junior
 *
 * @category   controller
 * @package    myfuses
 * @author     Flavio Goncalves Garcia <flavio.garcia at candango.org>
 * @author     Luiz Fernando Siroteau Serique Junior <marcelo.serique at gmail.com>
 * @copyright  Copyright (c) 2006 - 2009 Candango Open Source Group
 * @link       http://www.candango.org/myfuses
 * @license    http://www.mozilla.org/MPL/MPL-1.1.html  MPL 1.1
 * @version    SVN: $Id: MyFuses.class.php 642 2008-12-06 12:22:15Z flavio.garcia $
 */

/**
 * MyFusesPagingHandler - MyFusesPagingHandler.class.php
 *
 * Handles Pagination
 * 
 * PHP version 5
 *
 * @category   paging
 * @package    myfuses.util.paging
 * @author     Flavio Goncalves Garcia <flavio.garcia at candango.org>
 * @author     Luiz Fernando Siroteau Serique Junior <marcelo.serique at gmail.com>
 * @copyright  Copyright (c) 2006 - 2009 Candango Open Source Group
 * @link http://www.candango.org/myfuses
 * @license    http://www.mozilla.org/MPL/MPL-1.1.html  MPL 1.1
 * @version    SVN: $Revision: 642 $
 * @since      Revision 17
 */
class MyFusesPagingHandler {
    
    private static $queryCount;
    private static $regsPerPage;
    private static $pagesPerBlocks;
    private static $currentPage;
    private static $urlVariable;
    
    private static $urlQueryString;
    private static $href;
    
    private static $pageCount;
    private static $regsInLastPage;
    
    private static $blockCount;
    private static $pagesInLastBlock;
    
    
    private static $currentFirtsReg;
    private static $currentLastReg;
    
    private static $currentBlock;
    
    private static $currentFirtsPage;
    private static $currentLastPage;
    
    private static $paginationVariable;
    
    public static function getPaginationVariable() {
        return self::$paginationVariable;
    }
    
    public static function doPagination( $queryCount, $regsPerPage, 
        $pagesPerBlocks, $xfa, $currentPage = 1, $urlVariable = 'pag' ) {
        
        self::$paginationVariable = $urlVariable;
        
        if ( is_null( $currentPage ) ) {
            $currentPage = 1;
        }
        
        // setting initial properties 
        self::$queryCount = $queryCount;
        self::$regsPerPage = $regsPerPage;
        self::$pagesPerBlocks = $pagesPerBlocks;
        self::$currentPage = $currentPage;
        self::$urlVariable = $urlVariable;
        
        
        // TODO more href handler needed
        // definining href
        
        // Removing page indicator and fuseaction variable
        $fuseactionVar = MyFuses::getApplication()->getFuseactionVariable();
        
        $qStrPos = 0;
        $qFuseactionVarPos = 0;
        $vurlQueryString = explode( "&" , $_SERVER[ 'QUERY_STRING' ] );
        for( $i=0; $i < count( $vurlQueryString ); $i++ ) {
            if ( !( strpos( $vurlQueryString[ $i ], $urlVariable ) === false ) ) {
                $qStrPos = $i;
            }
            if ( !( strpos( $vurlQueryString[ $i ], MyFuses::getApplication()->getFuseactionVariable() ) === false ) ) {
                $qFuseactionVarPos = $i;
            }
        }
        
        self::$urlQueryString = str_replace( array( $vurlQueryString[ $qStrPos ] . "&", $vurlQueryString[ $qFuseactionVarPos ] . "&" )  , "" , $_SERVER['QUERY_STRING'] . "&" );
        //END Removing page indicator and fuseaction variable
        
        // Adding +'s into querystring
        self::$urlQueryString = str_replace( "%20"  , "+" , self::$urlQueryString ) ;
        
        while( substr( self::$urlQueryString, strlen( self::$urlQueryString ) - 1, 1 ) == '&'  ) {
            self::$urlQueryString = substr( self::$urlQueryString, 0 , strlen( self::$urlQueryString ) - 1 );
        }
        
        if ( ( substr( self::$urlQueryString, strlen( self::$urlQueryString ) - 1, 1 ) == '&' ) || ( strlen( self::$urlQueryString ) == 0 ) ) {
            self::$urlQueryString .= self::$urlVariable . '=' ;
        }
        else{
            self::$urlQueryString .=  '&' . self::$urlVariable . '=' ;
        }
        
        self::$href = MyFuses::getMySelfXfa( $xfa ) . "&" . self::$urlQueryString;
        
        // setting page count
        if( $queryCount == 0 ){
            self::$pageCount = 1;
        }
        else {
            self::$pageCount = (int) ( self::$queryCount / self::$regsPerPage ) + 
                ( ( self::$queryCount % self::$regsPerPage ) > 0 ? 1 : 0 );
        }
        
        if ( self::$pageCount < self::$currentPage || self::$currentPage == 0 ) {
            self::$currentPage = self::$pageCount; 
        }
        
        // setting registers in last page
        self::$regsInLastPage = ( int ) 
            ( ( self::$queryCount % self::$regsPerPage ) > 0 ? ( self::$queryCount % self::$regsPerPage ) : 
                self::$regsPerPage );
        
        // setting block count
        self::$blockCount = (int) ( self::$pageCount / self::$pagesPerBlocks ) + 
            ( ( self::$pageCount % self::$pagesPerBlocks ) > 0 ? 1 : 0 );
        
        // setting block count
        if ( self::$pageCount <  self::$pagesPerBlocks ) {
            self::$pagesInLastBlock = self::$pageCount;
        }
        else {
            self::$pagesInLastBlock = (int) 
                ( ( self::$pageCount % self::$pagesPerBlocks ) > 0 ? ( self::$pageCount % self::$pagesPerBlocks ) : 
                    self::$pagesPerBlocks );
        }
        
        // setting current first register
        self::$currentFirtsReg = ( ( self::$currentPage * self::$regsPerPage ) + 1 ) - self::$regsPerPage;
        // setting current last register
        if ( self::$currentPage == self::$pageCount ) {
            self::$currentLastReg = self::$currentFirtsReg + ( self::$regsInLastPage - 1 );
        }
        else {
            self::$currentLastReg = ( self::$currentPage * self::$regsPerPage );
        }
        
        
        // setting current block
        if ( self::$currentPage <= self::$pagesPerBlocks ) {
            self::$currentBlock = 1;
        }
        else {
            self::$currentBlock = (int) ( self::$currentPage / self::$pagesPerBlocks ) + 
                ( ( self::$currentPage % self::$pagesPerBlocks ) > 0 ? 1 : 0 );
        }
        
        // setting current first page
        self::$currentFirtsPage = ( ( self::$currentBlock * self::$pagesPerBlocks ) + 1 ) - self::$pagesPerBlocks;
        // setting current last register
        if ( self::$currentBlock == self::$blockCount ) {
            self::$currentLastPage = self::$currentFirtsPage + ( self::$pagesInLastBlock - 1 );
        }
        else {
            self::$currentLastPage = ( self::$currentBlock * self::$pagesPerBlocks );
        }
    }
       
    public static function getLinks( $firstPage = 'First Page', 
        $lastPage = 'Last Page', $previousBlock = '<<', 
        $nextBlock = '>>', $previousPage = '<', $nextPage = '>' ) {    
        
        if( self::getCurrentPage() > 1 ) {
            $links[] = self::getFirstPageLink( $firstPage );
        }
        
        if( self::getCurrentBlock() > 1 ) {
            $links[] = self::getPreviousBlockLink( $previousBlock );    
        }
        
        if( self::getCurrentPage() > 1 ) {
            $links[] = self::getPreviousPageLink( $previousPage );    
        }
        
        foreach( self::getLinkArray() as $link ) {
            $links[] = $link;
        }
        
        if( self::getCurrentPage() < self::getPageCount() ) {
            $links[] = self::getNextPageLink( $nextPage );    
        }
        
        if( self::getCurrentBlock() < self::getBlockCount() ) {
            $links[] = self::getNextBlockLink( $nextBlock );    
        }
        
        if( self::getCurrentPage() < self::getPageCount() ) {
            $links[] = self::getLastPageLink( $lastPage );
        }
        
        return $links;
    }
    
    
    
    private static function getLink( $page ) {
        if( $page == self::$currentPage ) {
            return $page;
        }
        else{
            return '<a href="' . self::$href. $page . '&" >' . $page . '</a>';
        }
    }
    
    public static function getQueryCount() {
        return self::$queryCount;
    }
    
    public static function getRegsPerPage(){
        return self::$regsPerPage;
    }
    
    public static function getPagesPerBlocks(){
        return self::$pagesPerBlocks;
    }
    
    public static function getCurrentPage(){
        return self::$currentPage;
    }
    
    public static function getUrlVariable(){
        return self::$urlVariable;
    }
    
    public static function getQueryString(){
        return self::$urlQueryString;
    }
    
    public static function getHref(){
        return self::$href;
    }
    
    public static function getPageCount(){
        return self::$pageCount;
    }
    
    public static function getRegsInLastPage(){
        return self::$regsInLastPage;
    }
    
    public static function getBlockCount(){
        return self::$blockCount;
    }
    
    public static function getPagesInLastBlock(){
        return self::$pagesInLastBlock;
    }
    
    public static function getCurrentFirstReg(){
        return self::$currentFirtsReg;
    }
    
    public static function getCurrentLastReg(){
        return self::$currentLastReg;
    }
    
    public static function getCurrentBlock(){
        return self::$currentBlock;
    }
    
    public static function getCurrentFirstPage(){
        return self::$currentFirtsPage;
    }
    
    public static function getCurrentLastPage(){
        return self::$currentLastPage;
    }
    
    public static function getLinkArray() {
        $linkArray = array();
        for ( $i = ( self::$currentFirtsPage - 1 ); $i < self::$currentLastPage; $i++ ) {
            
            $linkArray[ count( $linkArray ) ] = self::getLink( $i + 1 );
            
        }
        return $linkArray;
    }
    
    public static function getFirstPageLink( $label ) {
        if ( self::$currentPage == 1 ) {
            return $label;
        }
        else{
            return '<a href="' . self::$href. '1&" >' . $label . '</a>';
        }
    }
    
    public static function getPreviousBlockLink( $label ) {
        if ( self::$currentBlock == 1 ) {
            return $label;
        }
        else{
            return '<a href="' . self::$href. ( self::$currentFirtsPage - 1 ) . '&" >' . $label . '</a>';
        }
    }
    
    public static function getLastPageLink( $label ) {
        if ( self::$currentPage == self::$pageCount ) {
            return $label;
        }
        else{
            return '<a href="' . self::$href. self::$pageCount . '&" >' . $label . '</a>';
        }
    }
    
    public static function getPreviousPageLink( $label ) {
        if ( self::$currentPage == 1 ) {
            return $label;
        }
        else{
            return '<a href="' . self::$href. ( self::$currentPage - 1 ) . '&" >' . $label . '</a>';
        }
    }
    
    public static function getNextPageLink( $label ) {
        if ( self::$currentPage > self::$pageCount ) {
            return $label;
        }
        else{
            return '<a href="' . self::$href. ( self::$currentPage + 1 ) . '&" >' . $label . '</a>';
        }
    }
    
    public static function getNextBlockLink( $label ) {
        if ( self::$currentBlock == self::$blockCount ) {
            return $label;
        }
        else{
            return '<a href="' . self::$href. ( self::$currentLastPage + 1 ) . '&" >' . $label . '</a>';
        }
    }
    
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */