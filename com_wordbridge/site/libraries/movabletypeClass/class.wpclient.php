<?php

include("class.mtclient.php");

class wpclient extends mtclient {

    function wpclient($username, $password, $host, $pathToXMLRPC)
    {
        $this->bServer =  $host;
        $this->bPath = $pathToXMLRPC;
        $this->app = new xmlrpcval(null, "string");
        $this->username = new xmlrpcval($username, "string");
        $this->password = new xmlrpcval($password, "string");
    
        $this->bloggerclient($username, $password);
    }

    function getTags($blogID)
    {
        return $this->_genericWPMethod('getTags', $blogID);
    }

    function getPageList($blogID)
    {
        return $this->_genericWPMethod('getPageList', $blogID);
    }

    function getCategories($blogID)
    {
        return $this->_genericWPMethod('getCategories', $blogID);
    }

    function getAuthors($blogID)
    {
        return $this->_genericWPMethod('getAuthors', $blogID);
    }

    function getPageStatusList($blogID)
    {
        return $this->_genericWPMethod('getPageStatusList ', $blogID);
    }

    function getPageTemplates($blogID)
    {
        return $this->_genericWPMethod('getPageTemplates', $blogID);
    }

    function getCommentStatusList($blogID)
    {
        return $this->_genericWPMethod('getCommentStatusList', $blogID);
    }

    function getPostStatusList($blogID)
    {
        return $this->_genericWPMethod('getPostStatusList', $blogID);
    }

    function getCommentCount($blogID, $postID)
    {
        $XMLblogid = new xmlrpcval($blogID, "int");
        $XMLpostid = new xmlrpcval($postID, "string");
        $r = new xmlrpcmsg("wp.getCommentCount ", array($XMLblogid, $this->XMLusername, $this->XMLpassword, $XMLpostid));
        $r = $this->exec($r);
        return $r;
    }

    function _genericWPMethod($method, $blogID)
    {
        $XMLblogid = new xmlrpcval($blogID, "int");
        $r = new xmlrpcmsg("wp.$method", array($XMLblogid, $this->XMLusername, $this->XMLpassword));
        $r = $this->exec($r);
        return $r;
    }
}

?>
