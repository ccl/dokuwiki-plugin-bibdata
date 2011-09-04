<?php
/**
 * Bibdata Action Plugin:   Handle input from bibdata form.
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *
 * @author    Christoph Clausen <christoph.clausen@gmail.com>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'action.php';
require_once(DOKU_INC.'inc/media.php');
require_once(DOKU_INC.'inc/infoutils.php');

class action_plugin_bibdata extends DokuWiki_Action_Plugin {
    /**
     * Register its handlers with the DokuWiki's event controller
     */
    function register(&$controller) {
        $controller->register_hook('ACTION_HEADERS_SEND', 'BEFORE', $this,
                                   '_hook_handlepost');
    }

    /* Here the form data is treated in the following order:
     * 1) Check that page doesn't exist and is creatable
     * 2) Upload pdf
     * 3) Create target wiki page
     * 4) Insert BibTeX and other data
     */
    function _hook_handlepost(&$event, $param) {
        global $ID;

        if (!isset($_POST['bibdataform']) || !checkSecurityToken()) {
            return;
        }

        $ns = cleanID($_POST['targetns']);
        // Check that page not exists and is creatable
        $newid = cleanID($ns . ':' . $_POST['Page_id']);
        if(page_exists($newid)) {
            msg('bibdataform: target page already exists!', -1);
            return;
        }
        $auth = auth_quickaclcheck($newid);
        if($auth < AUTH_CREATE) {
            msg('bibdataform: user rights not sufficient to create target page!', -1);
            return;
        }

        // Upload file
        if(!$_FILES['upload']['error']) {
            $_POST['id'] = $_POST['Page_id'] . ".pdf";
            $res = media_upload($ns, auth_quickaclcheck($ns . ":*"));
            if(!$res) return;
        }

        // Create page contents
        $content = '<bibdata template=' . $_POST['template']
            . ' date=' . $_POST['Publication_date']
            . ' file=' . $_POST['id'] . ">\n"
            . trim($_POST['BibTeX_source']) . "\n"
            . "</bibdata>\n";
        saveWikiText($newid, $content, "created via Bibdata form.");
        $_POST['success'] = $newid;
    }
}
