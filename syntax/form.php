<?php
/**
 * Bibdata Form
 *
 * Inserts a form for creating a new bibdata entry in the
 * desired namespace.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christoph Clausen <christoph.clausen@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_bibdata_form extends DokuWiki_Syntax_Plugin {

    /**
     * Get the type of syntax this plugin defines.
     */
    function getType(){
	return 'substition';
    }

    /**
     * Define how this plugin is handled regarding paragraphs.
     */
    function getPType(){
	return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
	return 150;
    }


    /**
     * Connect lookup pattern to lexer.
     */
    function connectTo($mode) {
	$this->Lexer->addSpecialPattern('<bibdataform ?.*?>', $mode, 'plugin_bibdata_form');
    }

    /**
     * Handler to prepare matched data for the rendering process.
     */
    function handle($match, $state, $pos, &$handler){
	$data = array();
	$match = substr($match, 13, -1);
	$params = explode(" ", $match);
	foreach ($params as $p) {
	    list($key, $value) = explode("=", $p, 2);
	    $data[$key] = $value;
	}
	return $data;
    }

    /**
     * Handle the actual output creation.
     */
    function render($mode, &$R, $data) {
	$R->info['cache'] = false;
	if($mode == 'xhtml'){
	    $R->doc .= $this->_htmlform($data);
	    return true;
	}
	return false;
    }

    function _htmlform($data){
	global $ID;

	$form = new Doku_Form(array('class' => 'bibdataform_plugin bureaucracy__plugin',
				    'action' => wl($ID),
				    'enctype' => 'multipart/form-data'));
	$form->addElement(form_openfieldset(array('_legend' => 'Publication data', 'class' => 'bibdataform')));
	$form->addHidden('bibdataform', $ID);
	$form->addHidden('targetns', $data['targetns']);
	$form->addHidden('template', $data['template']);
	$form->addElement(form_makeOpenTag('label'));
	$form->addElement(form_makeOpenTag('span'));
	$form->addElement("BibTeX source");
	$form->addElement(form_makeCloseTag('span'));
	$form->addElement(form_makeOpenTag('textarea', array('name' => 'BibTeX source', 'id' => 'bibdataform__source')));
	$form->addElement($_POST['BibTeX_source']);
	$form->addElement(form_makeCloseTag('textarea'));
	$form->addElement(form_makeCloseTag('label'));
	$form->addElement(form_makeTextField('Page id', $_POST['Page_id'], 'Page id', '', 'edit', array('id' => 'bibdataform__pageid')));
	$form->addElement(form_makeTextField('Publication date', $_POST['Publication_date'] ? $_POST['Publication_date'] : 'YYYY-MM-DD', 'Publication date', '', 'edit', array('class' => 'datepicker edit')));
	$form->addElement(form_makeFileField('upload', 'PDF file', '', ''));
	$form->addElement(form_makeOpenTag('div', array('id' => 'bibdataform__status')));
	$form->addElement(form_makeCloseTag('div'));
	$form->addElement(form_makeOpenTag('div'));
	$form->addElement(form_makeButton('submit', '', 'Submit', array('id' => 'bibdataform__submit')));
	$form->addElement(form_makeCloseTag('div'));
	$form->endFieldset();
	return $form->getForm();
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8:
/* Local Variables: */
/* c-basic-offset: 4 */
/* End: */
