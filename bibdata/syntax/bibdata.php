<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christoph Clausen <christoph.clausen@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
$dataEntryFile = DOKU_PLUGIN.'datatemplate/syntax/entry.php';
if(file_exists($dataEntryFile)){
	require_once $dataEntryFile;
} else {
	msg('datatemplate: Cannot find Data plugin.', -1);
	return;
}
require_once "bibtexParse/PARSEENTRIES.php";
require_once "bibtexParse/PARSECREATORS.php";

class syntax_plugin_datatemplate_bibdata extends syntax_plugin_datatemplate_entry {


	/**
	 * Constructor. Load helper plugin
	 *
	 function syntax_plugin_datatemplate_bibdata(){
	 $this->dthlp =& plugin_load('helper', 'data');
	 if(!$this->dthlp) msg('Loading the data helper failed. Make sure the data plugin is installed.',-1);
	 }
	 */

	/**
	 * Connect pattern to lexer
	 */
	function connectTo($mode) {
		$this->Lexer->addSpecialPattern('<bibdata ?.*?>.*?</bibdata>', $mode, 'plugin_datatemplate_bibdata');
	}

	/**
	 * Handle the match - parse the data
	 */
	function handle($match, $state, $pos, &$handler){
		$num = preg_match('|<bibdata ?(.*?)>(.*?)</bibdata>|sm', $match, $matches);
		if($num == 0) return false;
		$params = explode(" ", $matches[1]);
		$bibtex = $matches[2];
		$dtsyntax = $this->_createDatatemplateSyntax($params, $bibtex);
		//return $dtsyntax;
		$data = parent::handle($dtsyntax, $state, $pos, $handler);
		$data['bibtex'] = $bibtex;
		return $data;
	}

	//quick function to better list authors' names
	//  it tries to correct PARSECREATOR weird way of handling spaces
	//  and sometimes mistaking first names for von tokens (typically for one-char names)
	function _listauthors($array){
		$goodstring="";
		foreach ($array as $author){
			$goodstring.=$author[0];
			$goodstring.=ltrim(chop($author[1]))?($author[0]?" ":"").ltrim(chop($author[1])):"";
			$goodstring.=$author[2]?" ".chop($author[2]):"";
			$goodstring.=$author[3]?" ".$author[3]:"";
			$goodstring.=", ";
		}
		return chop($goodstring, ', ');
	}

	//stupid way to remove extra surrounding curly braces
	function _cleancurl($x){
		return ltrim(chop($x,'}'),'{');
	}

	function _createDatatemplateSyntax($params, $bibtex) {
		// Parse parameters
		$out = "---- datatemplateentry publications ----\n";
		foreach ($params as $p) {
			list($key, $value) = explode("=", $p, 2);
			if($key == 'date') $key = 'date_dt';
			$out .= $key . ":" . $value . "\n";
		}
		// Parse bibtex
		$parse = NEW PARSEENTRIES();
		$parse->expandMacro = TRUE;
		$parse->loadBibtexString($bibtex);
		$parse->extractEntries();
		list($preamble, $strings, $entries, $undefinedStrings) = $parse->returnArrays();
		foreach ($entries as $entry){
			$a = new PARSECREATORS;
			$out .= ("title:      ".$this->_cleancurl($entry['title'])."\n");
			$out .= ("authors:    ".$this->_listauthors($a->parse($entry['author']))."\n");
			$out .= ("journal:    ".$this->_cleancurl($entry['journal'])."\n");
			$out .= ("volume:     ".$this->_cleancurl($entry['volume'])."\n");
			$out .= ("page:       ".$this->_cleancurl($entry['pages'])."\n");
			if(array_key_exists('doi', $entry))
				$out .= ("doi_url:    "."http://dx.doi.org/".$this->_cleancurl($entry['doi'])."\n");
			$out .= ("year:       ".$this->_cleancurl($entry['year'])."\n");
			$out .= ("abstract:   ".$this->_cleancurl($entry['abstract'])."\n");
		}
		$out .= "----\n";
		return $out;
	}

	function _showData($data, &$renderer) {
		parent::_showData($data, $renderer);
		$renderer->doc .= "<h1>BibTeX Source</h1>\n";
		$renderer->doc .= '<pre class="code bibtex">';
		$renderer->doc .= $data[bibtex];
		$renderer->doc .= '</pre>' . "\n";
	}
}
