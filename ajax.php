<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php'); //changelog.php=>addLogEntry(), io.php=>io_writeWikiPage(), pageinfo
require_once(DOKU_INC.'inc/auth.php');

$dataliststr = <<<EOD
---- datatemplatelist ----
template: tpl_publication_list
cols : %pageid%, title, journal, authors, volume, page, year, abstract
headers : id, title, journal, authors, volume, page, year, abstract
sort : ^date
filter : %pageid%~publications:bib:*
----
EOD;

// Let datatemplatelist handle syntax
$dtl =& plugin_load('syntax', 'datatemplate_list');
$data = $dtl->handle($dataliststr, DOKU_LEXER_SPECIAL, 0, $dtl); // Last parameter is not used, but need to pass some reference
$sqlite = $dtl->dthlp->_getDB();
if(!$sqlite) {
	echo "Could not connect to SQLite DB.";
	return;
}

$sql = $dtl->_buildSQL($data);
$headers = $data['headers'];
$res = $sqlite->query($sql);
$out = "[";
$rows = sqlite_fetch_all($res, SQLITE_NUM);
$row_cnt = count($rows);
for($i = 0; $i < $row_cnt; $i++) {
	$row = $rows[$i];
	$out .= "{";
	$cnt = count($row);
	foreach($headers as $num => $h) {
		$search = array("{", "}", '\\', '"');
		$replace = array("", "", "", '\"');
		if($h == "authors") {
			$search[] = "\n";
			$replace[] = ",";
		}
		$out .= "\"$h\"" . ": \"" . trim(str_replace($search, $replace, $row[$num])) . "\"";
		if($num < $cnt - 1) $out .= ",";
	}
	$out .= "}";
	if($i < $row_cnt -1) $out .= ",";
}
$out .= "]";
echo $out;
?>