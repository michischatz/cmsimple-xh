<?php

/*
 * @version $Id: admin.php 157 2013-09-21 09:14:20Z hi $
 */

/*
 * ==================================================================
 * Update-Check-Plugin for CMSimple_XH
 * ==================================================================
 * Version:    1.1
 * Build:      2013092101
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 *
 * Without written permission it is expressly prohibited to 
 * redistribute this software and to build, use and/or distribute 
 * modificated versions based on this software.
 * 
 * ==================================================================
 */

if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

if (defined('CMSIMPLE_RELEASE')) {
    $o .= '<div class="upd_error">'
            . 'Sorry, Update-Check-Plugin can only work with the original CMSimple_XH from '
            . '<a target="_blank" href="http://www.cmsimple-xh.org">www.cmsimple-xh.org.</a>'
            . tag('br')
            . 'Please delete the folder /plugins/hi_updatecheck/ from your installation.'
            . '</div>';
    return;
}

define('UPD_VERSION', '1.1');
define('UPD_DATE', '2013-09-21');

//Path to core-Versioninfo
define('CMSIMPLE_XH_VERSIONINFO', 'http://www.cmsimple-xh.org/downloads/versioninfo/cmsimple_xh-version.nfo');

include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
include_jQuery();

//Add hidden info-icon to editmenu
$o .= upd_addMenuEntry();

//Quick-Check, only once per session
if (!isset($_SESSION['upd_checked']) && $plugin_cf['hi_updatecheck']['autocheck'] == 'true') {
    hi_updateSetStatus();
}

if (isset($_SESSION['upd_available'])) {

    //Display info-icon in editmenu, if updates are available
    $o .= "\n";
    $o .= '<script type="text/javascript">
                    jQuery(document).ready(function($){
                        $("#editmenu_update").css("display","block");
                    });
            </script>' . "\n";

    //Prepend notification to "Sysinfo" - page if updates are available
    if (isset($_GET['sysinfo'])) {
        $upd_msg_sysinfo = '<div class="upd_info">'
                . '<b>' . $plugin_tx['hi_updatecheck']['message_sysinfo-update-found'] . '</b>'
                . tag('br')
                . '<a href="' . $sn . '?&amp;hi_updatecheck&amp;admin=plugin_main&amp;normal">' . $plugin_tx['hi_updatecheck']['message_sysinfo-link'] . '</a>'
                . '</div>';
        $o .= $upd_msg_sysinfo . "\n";
    }
}

/**
 * Handle the plugin administration.
 */
if (isset($hi_updatecheck)) {
    $o .= print_plugin_admin('on');
    switch ($admin) {
        case '':
            $o .= hi_updateVersion();
            break;
        case 'plugin_main':
            $o .= hi_updateCheckAll();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

/**
 * Returns the plugin version information view.
 *
 * @return string  The (X)HTML.
 */
function hi_updateVersion() {
    global $pth;

    return '<h1>CMSimple_XH - Update-Check</h1>' . "\n"
            . tag('img src="' . $pth['folder']['plugins'] . 'hi_updatecheck/images/software-update-icon.png" class="upd_plugin_icon"')
            . '<p>Version: ' . UPD_VERSION . ' - ' . UPD_DATE . '</p>' . "\n"
            . '<p>Copyright &copy;2013 <a href="http://cmsimple.holgerirmler.de/">Holger Irmler</a> - all rights reserved' . tag('br')
            . '<p class="upd_license">Permission is hereby granted, free of charge, to any person obtaining a copy of'
            . ' this unmodified software and associated documentation files (the "Software"), to use and copy'
            . ' the software, subject to the following conditions:</p>' . "\n"
            . '<p class="upd_license">The above copyright notice and this permission notice must be included in all'
            . ' copies of the Software.</p>' . "\n"
            . '<p class="upd_license">Without written permission it is expressly prohibited to redistribute this software'
            . ' and to build, use and/or distribute modificated versions based on this software.</p>' . "\n"
            . '<p class="upd_license">THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR'
            . ' IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,'
            . ' FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE'
            . ' AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER'
            . ' LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,'
            . ' OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE'
            . ' SOFTWARE.</p>' . "\n";
}

function hi_updateCheckAll() {
    global $plugin_cf, $plugin_tx, $pth, $tx;

    unset($_SESSION['upd_available']); //reset notifications
    include_once $pth['folder']['plugins'] . 'hi_updatecheck/updatecheck.php';
    $t = '<div "id=upd_list_container">';
    $t .= $plugin_tx['hi_updatecheck']['heading_updatecheck'];
    $temp = explode(',', $plugin_cf['hi_updatecheck']['ignore']);
    if (!in_array('CMSimple_XH', $temp)) {
        $t .= $plugin_tx['hi_updatecheck']['heading_updatecheck_core'];
        $t .= '<b>' . $tx['sysinfo']['version'] . ':</b>' . tag('br');
        $t .= CMSIMPLE_XH_VERSION;
        if (defined('CMSIMPLE_XH_DATE'))
            $t .= '&nbsp;&nbsp;Released: ' . CMSIMPLE_XH_DATE;
        $t .= '<ul class="upd_list">';
        $t .= '<li>';
        $t .= hi_updateCheck('CMSimple_XH', 0);
        $t .= '</li>';
        $t .= '</ul>';
    }
    $upd_plugins = hi_updateInstalledScripts();
    if (count($upd_plugins) > 0) {
        $t .= $plugin_tx['hi_updatecheck']['heading_updatecheck_plugins'];
        $t .= '<ul class="upd_list">';
        foreach ($upd_plugins as $value) {
            $t .= '<li>';
            $t .= hi_updateCheck($value, 0);
            $t .= '</li>';
        }
        $t .= '</ul>';
    }
    $t .= '</div>';
    return $t;
}

function hi_updateSetStatus() {
    global $o, $plugin_cf, $pth;

    include_once $pth['folder']['plugins'] . 'hi_updatecheck/updatecheck.php';
    $upd_plugins = hi_updateInstalledScripts();
    $temp = explode(',', $plugin_cf['hi_updatecheck']['ignore']);
    if (!in_array('CMSimple_XH', $temp)) {
        array_unshift($upd_plugins, 'CMSimple_XH');
    }
    foreach ($upd_plugins as $value) {
        $o .= hi_updateQuickCheck($value);
    }
    $_SESSION['upd_checked'] = TRUE;
}

//Add entry to editmenu if updates are available
function upd_addMenuEntry() {
    global $sn, $plugin_tx, $pth;

    $imgtag = tag('img src=\"' . $pth['folder']['plugins']
            . 'hi_updatecheck/images/update-available-24.png\" '
            . 'title=\"' . $plugin_tx['hi_updatecheck']['message_qc-update-found'] . '\" '
            . 'alt=\"' . $plugin_tx['hi_updatecheck']['message_qc-update-found'] . '\"'
    );
    $href = $sn . '?&amp;hi_updatecheck&amp;admin=plugin_main&amp;normal';
    $t = "\n";
    $t .= '<script type="text/javascript">
                    jQuery(document).ready(function($){
			//$("#editmenu_logout").after("<ul><li id=\"editmenu_update\"><a href=\"' . $href . '\">' . $imgtag . '<\/a></li></ul>");
                        $("#edit_menu").append("<li id=\"editmenu_update\"><a href=\"' . $href . '\">' . $imgtag . '<\/a></li>");
                    });
            </script>' . "\n";
    return $t;
}
?>