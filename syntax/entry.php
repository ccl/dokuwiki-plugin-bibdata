<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christoph Clausen <christoph.clausen@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
require_once(DOKU_PLUGIN.'syntax.php');
require_once "bibtexParse/PARSEENTRIES.php";
require_once "bibtexParse/PARSECREATORS.php";

class syntax_plugin_bibdata_entry extends DokuWiki_Syntax_Plugin {

	/**
     * will hold the datatemplate plugin
     */
    var $dtp = null;

	/**
	 * Constructor. Load datatemplate plugin.
	 */
	 function syntax_plugin_bibdata_entry(){
	 	$this->dtp =& plugin_load('syntax', 'datatemplate_entry');
	 	if(!$this->dtp) msg('Loading the datatemplate plugin failed. Make sure the data plugin is installed.',-1);
	 }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

   /**
     * Where to sort in?
     */
    function getSort(){
        return 155;
    }


	/**
	 * Connect pattern to lexer
	 */
	function connectTo($mode) {
		$this->Lexer->addSpecialPattern('<bibdata ?.*?>.*?</bibdata>', $mode, 'plugin_bibdata_entry');
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
		$data = $this->dtp->handle($dtsyntax, $state, $pos, $handler);
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
			$goodstring.=$author[3]?" ".$author[3]:"";
			$goodstring.=$author[2]?" ".chop($author[2]):"";
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

	function render($format, &$renderer, $data) {
	    global $ID;
		$return = $this->dtp->render($format, $renderer, $data);
		if($format == 'xhtml') {
			$renderer->doc .= "<h1>BibTeX Source</h1>\n";
			$raw = "<code bibtex>\n";

    		$descriptorspec = array(
               0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
               1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            );

            $process = proc_open('bibclean -max-width 100', $descriptorspec, $pipes);

            if (is_resource($process)) {
                // $pipes now looks like this:
                // 0 => writeable handle connected to child stdin
                // 1 => readable handle connected to child stdout
                // Any error output will be appended to /tmp/error-output.txt

                fwrite($pipes[0], $data[bibtex]);
                fclose($pipes[0]);

                $cleanbib = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                // It is important that you close any pipes before calling
                // proc_close in order to avoid a deadlock
                $return_value = proc_close($process);

                $raw .= $cleanbib;
            } else {
			    $raw .= $data[bibtex];
            }
			$raw .= '</code>' . "\n";
			$instr = p_get_instructions($raw);

            // render the instructructions on the fly
            $text = p_render('xhtml', $instr, $info);
			$renderer->doc .= $text;
		}
		return $return;
	}
}
