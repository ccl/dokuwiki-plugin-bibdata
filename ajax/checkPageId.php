<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/pageutils.php');

$pageid = urldecode($_POST['pageid']);
$targetns = urldecode($_POST['targetns']);

if(!page_exists($targetns . ':' . $pageid)) {
    echo 'true';
} else {
    echo 'false';
}