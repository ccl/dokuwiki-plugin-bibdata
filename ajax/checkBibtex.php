<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once("../syntax/bibtexParse/PARSEENTRIES.php");
require_once("../syntax/bibtexParse/PARSECREATORS.php");

$source = urldecode($_POST['source']);
$targetns = urldecode($_POST['targetns']);

$msg = '';
$has_doi = false;
$has_abstract = false;
$pageid = '';

$parse = new PARSEENTRIES();
$author_parser = new PARSECREATORS();
$parse->expandMacro = true;
$parse->loadBibtexString($source);
$parse->extractEntries();
list($preamble, $strings, $entries, $undefinedStrings) = $parse->returnArrays();
if(count($entries) == 0) {
    $msg = 'BibTeX does not contain any entries.';
} else {
    $e = $entries[0];
    $has_doi = array_key_exists('doi', $e);
    $has_abstract = array_key_exists('abstract', $e);
    $authors = $author_parser->parse($e['author']);
    $pageid = trim($authors[0][2]);
    if(array_key_exists('year', $e)) {
        $pageid .= $e['year'];
    }
    for($c = 'a'; $c != 'z'; $c++) {
        if(!page_exists($targetns . ':' . $pageid . $c)) break;
    }
    $pageid = cleanID($pageid . $c);
}

echo '{"msg": "'.$msg. '", "has_doi": ' . ($has_doi ? 'true' : 'false')
. ', "has_abstract": ' . ($has_abstract ? 'true' : 'false') . ', "pageid": "' . $pageid . '"}';
