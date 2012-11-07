<?php

/**
 * The main file of CMSimple_XH.
 *
 * @package	XH
 * @version	$CMSIMPLE_XH_VERSION$, $CMSIMPLE_XH_DATE$
 * @copyright	1999-2009 <http://cmsimple.org/>
 * @copyright	2009-2012 The CMSimple_XH developers <http://cmsimple-xh.com/?The_Team>
 * @license	http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3 
 * @version	$Id$
 * @link	http://cmsimple-xh.com
 */

/* utf8-marker = äöü */
/*
  ======================================
  $CMSIMPLE_XH_VERSION$
  $CMSIMPLE_XH_DATE$
  based on CMSimple version 3.3 - December 31. 2009
  For changelog, downloads and information please see http://www.cmsimple-xh.com
  ======================================
  -- COPYRIGHT INFORMATION START --

  CMSimple version 3.3 - December 31. 2009
  Small - simple - smart
  © 1999-2009 Peter Andreas Harteg - peter@harteg.dk

  -- COPYRIGHT INFORMATION END --

  -- LICENCE TYPES SECTION START --

  CMSimple is available under four different licenses:

  1) GPL 3
  From December 31. 2009 CMSimple is released under the GPL 3 licence with no link requirments. You may not remove copyright information from the files, and any modifications will fall under the copyleft conditions in the GPL 3.

  2) AGPL 3
  You must keep a convenient and prominently visible feature on every generated page that displays the CMSimple Legal Notices. The required link to the CMSimple Legal Notices must be static, visible and readable, and the text in the CMSimple Legal Notices may not be altered.
  See http://www.cmsimple.org/?Licence:CMSimple_Legal_Notices

  3) Linkware / CMSimple Link Requirement Licence
  Same as AGPL, but instead of keeping a link to the CMSimple Legal Notices, you must place a static, visible and readable link to www.cmsimple.org with the text or an image stating "Powered by CMSimple" on every generated page (place it in the template).
  See http://www.cmsimple.org/?Licence:CMSimple_Link_Requirement_Licence

  4) Commercial Licence
  This licence will allow you to remove the CMSimple Legal Notices / "Powered by CMSimple"-link at one specific domain. This licence will also protect your modifications against the copyleft requirements in AGPL 3 and give access to registering in user support forum.

  You may change this LICENCE TYPES SECTION to relevant information, if you have purchased a commercial licence, but then the files may not be distributed to any other domain not covered by a commercial licence.

  For further informaion about the licence types, please see http://www.cmsimple.org/?Licence and /cmsimple/legal.txt

  -- LICENCE TYPES SECTION END -
  ======================================
 */

if (preg_match('/cms.php/i', $_SERVER['PHP_SELF']))
    die('Access Denied');

/**
 * The title of the current page.
 *
 * @global  string $title
 */
$title = '';

/**
 * The (X)HTML for the contents area.
 *
 * @global  string $o
 */
$o = '';

/**
 * The (X)HTML for the <li>s holding error messages.
 *
 * @global  string $e
 */
$e = '';

/**
 * (X)HTML that will be inserted to the <head> section.
 *
 * @global  string $hjs
 */
$hjs = '';

/**
 * (X)HTML that will be inserted right before the </body> tag.
 *
 * @global string $bjs
 */
$bjs = '';

/**
 * JS for the onload event of the <body> element.
 *
 * @global string $onload
 */
$onload = '';


define('CMSIMPLE_XH_VERSION', '$CMSIMPLE_XH_VERSION$');
define('CMSIMPLE_XH_BUILD', '$CMSIMPLE_XH_BUILD$');
define('CMSIMPLE_XH_DATE', '$CMSIMPLE_XH_DATE$');

if (!defined('E_DEPRECATED')) {
    /**
     * @ignore
     */
    define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
    /**
     * @ignore
     */
    define('E_USER_DEPRECATED', 16384);
}

/**
 * A two dimensional array that holds the paths of important files and folders.
 *
 * @global array $pth
 */
$pth['file']['execute'] = './index.php';
$pth['folder']['content'] = './content/';
$pth['file']['content'] = $pth['folder']['content'] . 'content.htm';
$pth['file']['pagedata'] = $pth['folder']['content'] . 'pagedata.php';

$pth['folder']['base'] = is_dir('./cmsimple') ? './' : '../'; 
 
$pth['folder']['cmsimple'] = $pth['folder']['base'] . 'cmsimple/';
$pth['folder']['classes'] = $pth['folder']['cmsimple'] . 'classes/';
$pth['folder']['plugins'] = $pth['folder']['base'] . 'plugins/';

$pth['file']['log'] = $pth['folder']['cmsimple'] . 'log.txt';
$pth['file']['cms'] = $pth['folder']['cmsimple'] . 'cms.php';
$pth['file']['config'] = $pth['folder']['cmsimple'] . 'config.php';

require_once $pth['folder']['cmsimple'] . 'functions.php';
require_once $pth['folder']['cmsimple'] . 'tplfuncs.php';
require_once $pth['folder']['classes'] . 'PasswordHash.php';
require_once $pth['folder']['classes'] . 'page_data_router.php';
require_once $pth['folder']['classes'] . 'page_data_model.php';
require_once $pth['folder']['classes'] . 'page_data_views.php';
require_once $pth['folder']['plugins'] . 'utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';
require_once UTF8 . '/utils/validation.php';

if (file_exists($pth['folder']['cmsimple'].'defaultconfig.php')) {
    include($pth['folder']['cmsimple'].'defaultconfig.php');
}
if (!include($pth['file']['config']))
    die('Config file missing');
    
foreach (array('userfiles', 'downloads', 'images', 'media') as $temp) {
    if (!isset($cf['folders'][$temp])) { // for compatibility with older version's config files
	$cf['folders'][$temp] = $temp != 'media' ? "$temp/" : 'downloads/';
    }
    $pth['folder'][$temp] = $pth['folder']['base'] . $cf['folders'][$temp];
}

$pth['folder']['flags'] = $pth['folder']['images'] . 'flags/';

/**
 * Debug output generated by PHP according to debug mode.
 *
 * @global array $errors
 */
$errors = array();
xh_debugmode();

$pth['folder']['language'] = $pth['folder']['cmsimple'] . 'languages/';
$pth['folder']['langconfig'] = $pth['folder']['cmsimple'] . 'languages/';

/**
 * The current language (or subsite).
 *
 * @global string $sl
 */
if (preg_match('/\/[A-z]{2}\/[^\/]*/', sv('PHP_SELF'))) {
    $sl = strtolower(preg_replace('/.*\/([A-z]{2})\/[^\/]*/', '\1', sv('PHP_SELF')));
}
$temp = explode('/', str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
$temp = $temp[count($temp) - 2];
if (is_file('./cmsimplesubsite.htm')) {
    $sl = $temp;
}
if (!isset($sl)) {
    $sl = $cf['language']['default'];
}
    
$pth['file']['language'] = $pth['folder']['language'] . basename($sl) . '.php';
$pth['file']['langconfig'] = $pth['folder']['language'] . basename($sl) . 'config.php';
$pth['file']['corestyle'] = $pth['folder']['base'] . 'css/core.css';

XH_createLanguageFile($pth['file']['language']);
if (!is_readable($pth['file']['language']) && !is_readable($pth['folder']['language'].'default.php')) {
    die('Language file ' . $pth['file']['language'] . ' missing');
}

XH_createLanguageFile($pth['file']['langconfig']);
if (!is_readable($pth['file']['langconfig']) && !is_readable($pth['folder']['language'].'defaultconfig.php')) {
    die('Language config file ' . $pth['file']['langconfig'] . ' missing');
}

include $pth['folder']['language'] . 'default.php';
include $pth['file']['language'];
include $pth['folder']['language'] . 'defaultconfig.php';
include $pth['file']['langconfig'];

$pth['folder']['templates'] = $pth['folder']['base'] . 'templates/';
$pth['folder']['template'] = $pth['folder']['templates'] . $cf['site']['template'] . '/';

$temp = $txc['subsite']['template'] == ''
    ? $cf['site']['template']
    : $txc['subsite']['template'];
$pth['folder']['template'] = $pth['folder']['templates'] . $temp . '/';
$pth['file']['template'] = $pth['folder']['template'] . 'template.htm';
$pth['file']['stylesheet'] = $pth['folder']['template'] . 'stylesheet.css';
$pth['folder']['menubuttons'] = $pth['folder']['template'] . 'menu/';
$pth['folder']['templateimages'] = $pth['folder']['template'] . 'images/';


// don't check cookies, as these might be set from non UTF-8 scripts on the domain
// TODO: what about the variable names? what about other input (e.g. $_SERVER)?
XH_checkValidUtf8(array($_GET, $_POST));

/**
 * Whether the webserver is IIS.
 *
 * @global bool $iis
 */
$iis = strpos(sv('SERVER_SOFTWARE'), "IIS");

/**
 * Whether PHP is executed as (F)CGI.
 *
 * @global bool $cgi
 */
$cgi = (php_sapi_name() == 'cgi' || php_sapi_name() == 'cgi-fcgi');

/**
 * The relative path of the root folder, i.e. the site name.
 *
 * @global string $sn
 */
$sn = preg_replace('/([^\?]*)\?.*/', '\1', sv(($iis ? 'SCRIPT_NAME' : 'REQUEST_URI')));
foreach (array('action', 'download', 'downloads', 'edit', 'file', 'function',
	       'images', 'login', 'logout', 'mailform', 'media', 'normal',
	       'passwd', 'print', 'search', 'selected', 'settings', 'sitemap',
	       'stylesheet', 'text', 'userfiles', 'validate', 'xhpages') as $i) {
    initvar($i);
}

/**
 * The absolute path of the root folder.
 */
define('CMSIMPLE_ROOT', str_replace('index.php', '', str_replace('/' . $sl . '/', "/", $sn)));
/**
 * The relative path of the root folder.
 */
define('CMSIMPLE_BASE', $pth['folder']['base']);

/**
 * The current page's URL (selected URL).
 *
 * @global string $su
 */
$su = '';
if (sv('QUERY_STRING') != '') {
    $rq = explode('&', sv('QUERY_STRING'));
    if (!strpos($rq[0], '=')) {
        $su = $rq[0];
    }
    $v = count($rq);
    foreach ($rq as $i) {
        if (!strpos($i, '=')) {
            $GLOBALS[$i] = 'true';
        }
    }
} else {
    $su = $selected;
}
if (!isset($cf['uri']['length'])) {
    $cf['uri']['length'] = 200;
}
$su = substr($su, 0, $cf['uri']['length']);

/**
 * Requests the stylesheet.
 *
 * @global mixed $stylesheet
 */
if ($stylesheet != '') {
    header("Content-type: text/css");
    include($pth['file']['stylesheet']);
    exit;
}

/**
 * Requests a file download.
 *
 * @global string $download
 */
if ($download != '') {
    download($pth['folder']['downloads'] . basename($download));
}

$pth['file']['search'] = $pth['folder']['cmsimple'] . 'search.php';
$pth['file']['mailform'] = $pth['folder']['cmsimple'] . 'mailform.php';

/**
 * Whether the user is in admin mode.
 *
 * @global bool $adm
 */
$adm = 0;

/**
 * The requested function.
 *
 * @global string $f
 */
$f = '';

/**
 * The password hasher.
 *
 * @global object $xh_hasher
 */
$xh_hasher = new PasswordHash(8, true);

if ($txc['subsite']['password'] != "") {
    $cf['security']['password'] = $txc['subsite']['password'];
}


// LOGIN & BACKUP

$adm = gc('status') == 'adm' && logincheck();

if ($cf['security']['type'] == 'page' && $login && $passwd == '' && !$adm) {
    $login = null;
    $f = 'login';
}

if ($login && !$adm) {
    if ($xh_hasher->CheckPassword($passwd, $cf['security']['password'])
	&& ($cf['security']['type'] == 'page' || $cf['security']['type'] == 'javascript'))
    {
	setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
	setcookie('passwd', $cf['security']['password'], 0, CMSIMPLE_ROOT);
	$adm = true;
	$edit = true;
	writelog(date("Y-m-d H:i:s") . " from " . sv('REMOTE_ADDR') . " logged_in\n");
    } else {
	shead('403');
    }
} elseif ($logout && $adm) {
    $o .= XH_backup('content') . XH_backup('pagedata');
    $adm = false;
    setcookie('status', '', 0, CMSIMPLE_ROOT);
    setcookie('passwd', '', 0, CMSIMPLE_ROOT);
    $o .= '<p class="cmsimplecore_warning" style="text-align: center; font-weight: 900; padding: 8px;">' . $tx['login']['loggedout'] . '</p>';
}

/**
 * Whether the user is in admin mode. Prefer that over $adm, as variables can be changed.
 */
define('XH_ADM', $adm);

// SETTING FUNCTIONS AS PERMITTED

if ($adm) {
    $o .= '<script type="text/javascript">/* <![CDATA[ */'
	. 'if (document.cookie.indexOf(\'status=adm\') == -1)'
	. ' document.write(\'<div class="cmsimplecore_warning">' . $tx['error']['nocookies'] . '</div>\')'
	. '/* ]]> */</script>'
	. '<noscript><div class="cmsimplecore_warning">' . $tx['error']['nojs'] . '</div></noscript>';
    if ($edit) {
        setcookie('mode', 'edit', 0, CMSIMPLE_ROOT);
    }
    if ($normal) {
        setcookie('mode', '', 0, CMSIMPLE_ROOT);
    }
    if (gc('mode') == 'edit' && !$normal) {
        $edit = true;
    }
} else {
    if (gc('status') != '') {
        setcookie('status', '', 0, CMSIMPLE_ROOT);
    }
    if (gc('passwd') != '') {
        setcookie('passwd', '', 0, CMSIMPLE_ROOT);
    }
    if (gc('mode') == 'edit') {
        setcookie('mode', '', 0, CMSIMPLE_ROOT);
    }
}

/**
 * The number of pages.
 *
 * @global int $cl
 */
$cl = 0;
rfc(); // Here content is loaded

if ($function == 'search') {
    $f = 'search';
}
if ($mailform || $function == 'mailform') {
    $f = 'mailform';
}
if ($sitemap) {
    $f = 'sitemap';
}
if ($xhpages) {
    $f = 'xhpages';
}

if (is_readable($pth['folder']['cmsimple'] . 'userfuncs.php')) {
    include_once $pth['folder']['cmsimple'] . 'userfuncs.php';
}

// copies title, keywords and description from $txc to $cf

foreach ($txc['meta'] as $i => $j) {
    if (strlen(trim($j)) > 0 && $i != 'codepage') {
        $cf['meta'][$i] = $j;
    }
}
foreach ($txc['site'] as $i => $j) {
    if (strlen(trim($j)) > 0) {
        $cf['site'][$i] = $j;
    }
}
foreach ($txc['mailform'] as $i => $j) {
    if (strlen(trim($j)) > 0) {
        $cf['mailform'][$i] = $j;
    }
}

if (strcasecmp($tx['meta']['codepage'], 'UTF-8') != 0) {
    $e .= '<li>' . sprintf('<b>UTF-8 encoding required, but codepage %s found!</b>', $tx['meta']['codepage']) . tag('br')
	. 'Please change that in Settings&rarr;Language&rarr;Meta&rarr;Codepage'
	. ' and convert all files to UTF-8 without BOM, if not already done.</li>' . "\n";
}

// Plugin loading
if ($function == 'save') {
    $edit = true;
}

if ($adm) {
    include_once $pth['folder']['cmsimple'] . 'adminfuncs.php';
}

/**
 * For compatibility with plugins.
 */
define('PLUGINLOADER', TRUE);
/**
 * For compatibility with plugins.
 */
define('PLUGINLOADER_VERSION', 2.111);


/**
 * A unique prefix for autogenerated forms.
 */
define('XH_FORM_NAMESPACE', 'PL3bbeec384_');


// If admin is logged in, generate fake output to suppress later adjustment of $s.
if ($adm) {
    $o .= ' ';
}


// BOF page_data


/*
 * Check if page-data-file exists, if not: try to
 * create a new one with basic data-fields.
 */
if (!file_exists($pth['file']['pagedata'])) {
    $temp = XH_writeFile($pth['file']['pagedata'],
			 '<?php' . "\n" . '$page_data_fields[] = \'url\';' . "\n"
			 . '$page_data_fields[] = \'last_edit\';' . "\n" . '?>');
    if ($temp !== false) {
	chmod($pth['file']['pagedata'], 0666);
    } else {
	e('cntsave', 'pagedata', $pth['file']['pagedata']);
    }
}

/**
 * The page data router.
 *
 * @global object $pd_router
 */
$pd_router = new PL_Page_Data_Router($pth['file']['pagedata'], $h);

if ($adm) {

    /**
     * Check for any changes to handle
     * First: check for changes from texteditor
     */
    if ($function == 'save') {
        /**
         * Collect the headings and pass them over to the router
         */
	$temp = $cf['menu']['levels'];
        $text = preg_replace("/<h[1-" . $temp . "][^>]*>(&nbsp;|&#160;|\xC2\xA0| )?<\/h[1-" . $temp . "]>/is",
			     '', stsl($text));
        preg_match_all('/<h[1-' . $temp . '].*>(.+)<\/h[1-' . $temp . ']>/isU',
		       $text, $matches);
        $pd_router->refresh_from_texteditor($matches[1], $s);
    }

    /**
     * Second: check for changes from MenuManager
     */
    if (isset($menumanager) && $menumanager == 'true'
	&& $action == 'saverearranged' && !empty($text))
    {
        $pd_router->refresh_from_menu_manager($text);
    }

    /**
     * Finally check for some changed page infos
     */
    if ($s > -1 && isset($_POST['save_page_data'])) {
        $temp = $_POST;
        unset($temp['save_page_data']);
	$temp = array_map('stsl', $temp);
        $pd_router->update($s, $temp);
    }
}
/**
 * The number of the currently selected page.
 *
 * @global int $pd_s
 */
$pd_s = $s == -1 && !$f && $o == '' && $su == '' ? 0 : $s;

/**
 * The infos about the current page.
 *
 * @global object $pd_current
 */
$pd_current = $pd_router->find_page($pd_s);

// EOF page_data

/*
 * Include plugin (and plugin files)
 */
foreach (XH_plugins() as $plugin) {
    PluginFiles($plugin);
    if (is_readable($pth['file']['plugin_classes'])) {
	include_once $pth['file']['plugin_classes'];
    }
}

foreach (XH_plugins() as $plugin) {
    PluginFiles($plugin);

    // Load plugin config
    if (is_readable($pth['folder']['plugin_config'] . 'defaultconfig.php')) {
	include $pth['folder']['plugin_config'] . 'defaultconfig.php';
    }
    if (is_readable($pth['file']['plugin_config'])) {
	include $pth['file']['plugin_config'];
    }
    
    // Load plugin language
    XH_createLanguageFile($pth['file']['plugin_language']);
    if (is_readable($pth['folder']['plugin_languages'] . 'default.php')) {
        include $pth['folder']['plugin_languages'] . 'default.php';
    }
    if (is_readable($pth['file']['plugin_language'])) {
	include $pth['file']['plugin_language'];
    }

    // Load plugin index.php
    if (is_readable($pth['file']['plugin_index'])) {
	include $pth['file']['plugin_index'];
    }

    // Add plugin css to the header of CMSimple/Template
    if (is_file($pth['file']['plugin_stylesheet'])) {
	$hjs .= tag('link rel="stylesheet" href="' . $pth['file']['plugin_stylesheet']
		    . '" type="text/css"') . "\n";
    }
}


/*
 * Load admin functions (admin.php, if exists) of plugin
 */
if ($adm) {
    foreach (XH_plugins(true) as $plugin) {
	PluginFiles($plugin);
	if (is_readable($pth['file']['plugin_admin'])) {
	    include $pth['file']['plugin_admin'];
	}
    }
    $o .= $pd_router->create_tabs($s);
}

/*
 * Pre-Call Plugins
 */
preCallPlugins(); // TODO: when is CMSimple scripting evaluated?

unset($plugin);


if ($f == 'search') {
    @include $pth['file']['search'];
}
if ($f == 'mailform' && !empty($cf['mailform']['email'])) {
    include $pth['file']['mailform'];
}
if ($f == 'sitemap') {
    $title = $tx['title'][$f];
    $ta = array();
    $o .= '<h1>' . $title . '</h1>' . "\n";
    for ($i = 0; $i < $cl; $i++) {
        if (!hide($i) || $cf['show_hidden']['pages_sitemap'] == 'true') {
            $ta[] = $i;
        }
    }
    $o .= li($ta, 'sitemaplevel');
}

// Compatibility for DHTML menus
$si = -1;
$hc = array();
for ($i = 0; $i < $cl; $i++) {
    if (!hide($i) || ($i == $s && $cf['show_hidden']['pages_toc'] == 'true')) {
        $hc[] = $i;
    }
    if ($i == $s) {
        $si = count($hc);
    }
}
$hl = count($hc);

// LEGAL NOTICES - not needed under GPL3
if (empty($cf['menu']['legal'])) {
    $cf['menu']['legal'] = 'CMSimple Legal Notices';
}
if ($su == uenc($cf['menu']['legal'])) {
    $f = $title = $cf['menu']['legal'];
    $s = -1;
    $o .= '<h1>' . $title . '</h1>' . rf($pth['folder']['cmsimple'] . 'legal.txt');
}

if ($adm) {
    if ($validate) {
	$f = 'validate';
    }
    if ($settings) {
	$f = 'settings';
    }
    if (isset($sysinfo)) {
        $f = 'sysinfo';
    }
    if (isset($phpinfo)) {
        $f = 'phpinfo';
    }
    if ($file) {
        $f = 'file';
    }
    // FIXME: handling of userfiles, images and download probably not necessary,
    //		as this should already be handled by the filebrowser
    if ($userfiles) {
        $f = 'userfiles';
    }
    if ($images || $function == 'images') {
        $f = 'images';
    }
    if ($downloads || $function == 'downloads') {
        $f = 'downloads';
    }
    if ($function == 'save') {
        $f = 'save';
    }

    if ($f == 'settings' || $f == 'images' || $f == 'downloads'
        || $f == 'validate' || $f == 'sysinfo' || $f == 'phpinfo')
    {
        $title = $tx['title'][$f];
        $o .= "\n\n" . '<h1>' . $title . '</h1>' . "\n";
    }

    switch ($f) {
    case 'sysinfo':
        $o .= XH_sysinfo();
        break;
    case 'phpinfo':
        phpinfo();
        exit;
    case 'settings':
        $o .= XH_settingsView();
        break;
    case 'file':
        if (preg_match('/^\d{8}_\d{6}_(?:content.htm|pagedata.php)$/', $file)) {
            $pth['file'][$file] = $pth['folder']['content'] . '/' . $file;
        }
        if ($pth['file'][$file] != '') {
            if ($action == 'view') {
                header('Content-Type: text/plain; charset=utf-8');
                echo rmnl(rf($pth['file'][$file]));
                exit;
            }
            if ($action == 'download') {
                download($pth['file'][$file]);
            } else {
                include_once $pth['folder']['classes'] . 'FileEdit.php';
                $temp = array('config' => 'XH_CoreConfigFileEdit',
                              'langconfig' => 'XH_CoreLangconfigFileEdit',
                              'language' => 'XH_CoreLangFileEdit',
                              'template' => 'XH_CoreTextFileEdit',
                              'stylesheet' => 'XH_CoreTextFileEdit');
                $temp = array_key_exists($file, $temp) ? $temp[$file] : null;
                if ($action == 'save') {
                    $o .= $temp->submit();
                } else {
                    $o .= $temp->form();
                }
            }
        }
        break;
    case 'validate':
        include_once $pth['folder']['classes'] . 'LinkCheck.php';
        $temp = new XH_LinkCheck();
        $o .= $temp->check_links();
        break;
    }
}


// fix $s
if ($s == -1 && !$f && $o == '' && $su == '') {
    $s = 0;
    $hs = 0;
}

// SAVE

if ($adm && $f == 'save') {
    $c[$s] = $text;

    if ($s == 0) {
        if (!preg_match("/^<h1[^>]*>.*<\/h1>/i", rmanl($c[0]))
            && !preg_match("/^(<p[^>]*>)?(\&nbsp;| |<br \/>)?(<\/p>)?$/i", rmanl($c[0])))
        {
            $c[0] = '<h1>' . $tx['toc']['missing'] . '</h1>' . "\n" . $c[0];
        }
    }
    $title = utf8_ucfirst($tx['filetype']['content']);

    if ($fh = @fopen($pth['file']['content'], "w")) {
        fwrite($fh, '<html><head><title>Content</title></head><body>' . "\n");
        foreach ($c as $i) {
            fwrite($fh, rmnl($i . "\n"));
        }
        fwrite($fh, '</body></html>');
        fclose($fh);

        preg_match('~<h[1-'.$cf['menu']['levels'].'][^>]*>(.+?)</h[1-'.$cf['menu']['levels'].']>~isu', $c[$s], $matches);
        if (count($matches) > 0) {
            $temp = explode($cf['uri']['seperator'], $selected);
            array_splice($temp, -1, 1, uenc(trim(xh_rmws(strip_tags($matches[1])))));
            $su = implode($cf['uri']['seperator'], $temp);
        } else {
            $su = $u[max($s - 1, 0)];
        }
        header("Location: " . $sn . "?" . $su);
        exit;
    } else {
        e('cntwriteto', 'content', $pth['file']['content']);
    }
    $title = '';
}

if ($adm && $edit && (!$f || $f == 'save') && !$download) {
    if ($s > -1) {
        $o .= XH_contentEditor();
    } else {
        $o .= '<p>' . $tx['error']['cntlocateheading'] . '</p>' . "\n";
    }
}

if ($adm && ($images || $downloads || $userfiles || $media || $edit && (!$f || $f == 'save') && !$download))
{
    if ($cf['filebrowser']['external'] && !file_exists($pth['folder']['plugins'] . $cf['filebrowser']['external'])) {
        $e .= '<li>' . sprintf('External filebrowser %s missing', $cf['filebrowser']['external']) . '</li>' . "\n"; // FIXME: i18n
    }
}

if ($adm && $f == 'xhpages') {
    if ($cf['pagemanager']['external'] && !file_exists($pth['folder']['plugins'] . $cf['pagemanager']['external'])) {
        $e .= '<li>' . sprintf('External pagemanager %s missing', $cf['pagemanager']['external']) . '</li>' . "\n"; // FIXME: i18n
    }
}



// CMSimple scripting
if (!($edit && $adm) && $s > -1) {
    $c[$s] = evaluate_cmsimple_scripting($c[$s]);
    if (isset($keywords)) {
	$cf['meta']['keywords'] = $keywords;
    }
    if (isset($description)) {
	$cf['meta']['description'] = $description;
    }
}


if ($s == -1 && !$f && $o == '') {
    shead('404');
}

loginforms();

// FIXME: why so late? Why at all?
foreach (array('content', 'pagedata', 'config', 'language', 'langconfig', 'stylesheet', 'template', 'log') as $i) {
    chkfile($i, (($login || $settings) && $adm));
}
if ($e) {
    $o = '<div class="cmsimplecore_warning cmsimplecore_center">' . "\n"
	. '<b>' . $tx['heading']['warning'] . '</b>' . "\n" . '</div>' . "\n"
	. '<ul>' . "\n" . $e . '</ul>' . "\n" . $o;
}
if ($title == '') {
    if ($s > -1) {
        $title = $h[$s];
    } elseif ($f != '') {
        $title = ucfirst($f); // FIXME: check for duplication
    }
}

if (!headers_sent($temp, $i)) {
    header('Content-Type: text/html; charset=' . $tx['meta']['codepage']);
} else {
    $temp .= ':' . $$i;
    exit(str_replace('{location}', $temp, $tx['error']['headers']));
}

if ($print) {
    if ($cf['xhtml']['endtags'] == 'true') {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"',
	    ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', "\n",
	    '<html xmlns="http://www.w3.org/1999/xhtml">', "\n";
    } else {
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"',
	    ' "http://www.w3.org/TR/html4/loose.dtd">', "\n", '<html>', "\n";
    }
    echo '<head>', "\n" . head(),
	'<meta name="robots" content="noindex">', "\n",
	'</head>', "\n", '<body class="print"', onload(), '>', "\n",
	content(), '</body>', "\n", '</html>', "\n";
    exit;
}


if (!XH_ADM && $adm) { // somebody has manipulated $adm!!!
    // TODO: better redirect to login page?
    $s = -1;
    $adm = $edit = false;
    $o = '';
    $f = 'login';
    loginforms();
}

ob_start('final_clean_up');

if (!include $pth['file']['template']) {
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain; charset=utf-8');
    echo $tx['error']['missing'], ' ', $tx['filetype']['template'], "\n",
	$pth['file']['template'];
    exit;
}

?>
