<?php

/*

	Admin panel v1.06

	Copyright (C) 2005-2014 ScriptsMill

	E-Mail: info@scriptsmill.com
	URL: http://www.scriptsmill.com
	Author: Stanislav Perederiy

    This file is part of ScriptsMill Comments.

    ScriptsMill Comments is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2.1 of the License, or
    (at your option) any later version.

    ScriptsMill Comments is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ScriptsMill Comments; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


*/

ob_start();

include("./config.php");
include("./lang/lang_{$COM_CONF['lang']}.php");


$comments_db_link = mysql_connect($COM_CONF['dbhost'],$COM_CONF['dbuser'],$COM_CONF['dbpassword']);
mysql_select_db($COM_CONF['dbname'], $comments_db_link);

$auth = is_auth();
main();

ob_end_flush();

function main() {

	global $auth;

        if ($_REQUEST['action'] == 'delete' && $auth){
        	delete();
        }
        elseif ($_REQUEST['action'] == 'list' && $auth){
        	view_list();
        }
        elseif ($_REQUEST['action'] == 'banip' && $auth){
        	banip();
        }
        elseif ($_REQUEST['action'] == 'search' && $auth){
        	search();
        }
        elseif ($_REQUEST['action'] == 'bannedlist' && $auth){
        	bannedlist();
        }
        elseif ($_REQUEST['action'] == 'unbanip' && $auth){
        	unbanip();
        }
        elseif ($_REQUEST['action'] == 'junk_list' && $auth){
        	view_junk_list();
        }
        elseif ($_REQUEST['action'] == 'not_junk' && $auth){
        	mark_as_not_junk();
        }
        elseif ($_REQUEST['action'] == 'clear_junk' && $auth){
        	clear_junk();
        }
        elseif ($_REQUEST['action'] == 'logout'){
        	logout();
        }
	elseif (1) {
		login_screen();
	}

}

function is_auth() {

	global $COM_CONF;

	if ($_COOKIE['login']) {
		$login = $_COOKIE['login'];
	}
	if ($_POST['login']) {
		$login = $_POST['login'];
	}
	if ($_COOKIE['passw']) {
		$passw = $_COOKIE['passw'];
	}
	if ($_POST['passw']) {
		$passw = $_POST['passw'];
	}

	if ($login == $COM_CONF['admin_name'] && $passw == $COM_CONF['admin_passw'] && $_REQUEST['action'] != 'logout') {
		setcookie("login", $login, time()+999999, "{$COM_CONF['script_dir']}/");
		setcookie("passw", $passw, time()+999999, "{$COM_CONF['script_dir']}/");
		return 1;
	}
	else {
		return 0;
	}

}

function login_screen() {

	global $auth, $COM_CONF, $COM_LANG;

	if ($auth) {
		search();
		return 0;
	}
	else {
		require("./templates/admin/default_login.php");
	}

}

function make_pages_string ($all_count, $records_per_page, $cur_page, $base_url) {

	if ($all_count > $records_per_page) {
		if ($cur_page > 0) { $cur_page=$cur_page-1; }
		$first_record = ($cur_page) * $records_per_page;
		$limit_string = "LIMIT $first_record, $records_per_page";
		$pages=$all_count/$records_per_page;
		if ($pages > (int) $pages) { $pages=(int)$pages+1; }
	}
	if ($pages>1) {
		$pages_string.="Page: ";
		if ($cur_page>10 && $pages>20) { $first_page=$cur_page-9; }
		else { $first_page=1; }
		if ($pages>20 && ($cur_page+10)<$pages) { $last_page=$first_page+19; }
		else { $last_page=$pages; }
		if ($cur_page+1>1) {
			$prev=$cur_page;
			$pages_string.="<a href='$base_url&page=$prev'>&lt</a>&nbsp;&nbsp;";
		}
		for ($i=$first_page; $i<=$last_page; $i++){
			if ($i != $cur_page+1) {
				$pages_string.="<a href='$base_url&page=$i'>$i</a>&nbsp; ";
			}
			else {
				$pages_string.="<b>$i</b>&nbsp; ";
			}
		}
		if ($cur_page+1<$pages) {
			$next=$cur_page+2;
				$pages_string.="<a href='$base_url&page=$next'>&gt</a>&nbsp&nbsp";
			}


	}
	return array ($pages_string, $limit_string);
}

function search() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$query = mysql_escape_string($_REQUEST['query']);

	$result = mysql_query("select href from {$COM_CONF['dbmaintable']} WHERE href like '%{$query}%' GROUP BY href");
	$all_count = mysql_num_rows($result);

	list ($pages_string, $limit_string) = make_pages_string ($all_count, 30, $_REQUEST['page'], "{$COM_CONF['admin_script_url']}?action=search&query=$query");

	$result = mysql_query("select href, COUNT(*) as count, MAX(time) as maxtime from {$COM_CONF['dbmaintable']} WHERE href like '%{$query}%' GROUP BY href ORDER BY maxtime DESC {$limit_string}", $comments_db_link);
	$href=$count=array();
	$hrefs_count=0;
	while (list($href[$hrefs_count], $count[$hrefs_count]) = mysql_fetch_row($result)){
		$hrefs_count++;
	}

	require("./templates/admin/default_search.php");


}

function view_list() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

//	preg_match ("/href=(.*)$/", $_SERVER['QUERY_STRING'], $matches);
//	$request_uri = $matches[1];
	$request_uri = $_REQUEST['href'];

	$result = mysql_query("select COUNT(id) from {$COM_CONF['dbmaintable']} where href='$request_uri'", $comments_db_link);
	list ($all_count) = mysql_fetch_row($result);
	list ($pages_string, $limit_string) = make_pages_string ($all_count, 30, $_REQUEST['page'], "{$COM_CONF['admin_script_url']}?action=list&href=$request_uri");

	$result = mysql_query("select id, time, text, author, email, dont_show_email, ip from {$COM_CONF['dbmaintable']} where href='$request_uri' order by time {$COM_CONF['sort_order']} $limit_string", $comments_db_link);

	$comments_count=0;
	$id=$time=$text=$author=$email=$dont_show_email=$ip=array();
	while (list($id[$comments_count], $time[$comments_count], $text[$comments_count], $author[$comments_count], $email[$comments_count], $dont_show_email[$comments_count], $ip[$comments_count])=mysql_fetch_array($result)) {
		$comments_count++;
	}

	require("./templates/admin/default_list.php");

}

function view_junk_list() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$result = mysql_query("select COUNT(id) from {$COM_CONF['dbjunktable']}", $comments_db_link);
	list ($all_count) = mysql_fetch_row($result);
	list ($pages_string, $limit_string) = make_pages_string ($all_count, 30, $_REQUEST['page'], "{$COM_CONF['admin_script_url']}?action=junk_list");

	$result = mysql_query("select id, time, text, author, email, dont_show_email, ip from {$COM_CONF['dbjunktable']} order by time {$COM_CONF['sort_order']} $limit_string", $comments_db_link);

	if (mysql_error($comments_db_link)) {
		echo mysql_error($comments_db_link);
	}


	$comments_count=0;
	$id=$time=$text=$author=$email=$dont_show_email=$ip=array();
	while (list($id[$comments_count], $time[$comments_count], $text[$comments_count], $author[$comments_count], $email[$comments_count], $dont_show_email[$comments_count], $ip[$comments_count])=mysql_fetch_array($result)) {
		$comments_count++;
	}

	require("./templates/admin/default_junk_list.php");

}

function delete() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$id = mysql_escape_string($_REQUEST['id']);

	mysql_query("delete from {$COM_CONF['dbmaintable']} where id='$id'", $comments_db_link);

	header("HTTP/1.1 302");
	header("Location: {$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=list&href={$_REQUEST['from']}");
	print "Comment has been deleted.<br><a href=\"{$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=list&href={$_REQUEST['from']}\">Click here to get back.</a>";


}

function banip() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$ip = mysql_escape_string($_REQUEST['ip']);

	mysql_query("INSERT INTO {$COM_CONF['dbbannedipstable']} SET ip='$ip'", $comments_db_link);

	print "IP {$_REQUEST['ip']} has been banned.<br><a href=\"{$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=list&href={$_REQUEST['from']}\">Click here to get back.</a>";


}

function bannedlist() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$result = mysql_query("SELECT ip FROM {$COM_CONF['dbbannedipstable']}", $comments_db_link);

	$ips_count=0;
	while (list($ip[$ips_count]) = mysql_fetch_row($result)) {
		$ips_count++;
	}

	require ("./templates/admin/default_blist.php");

}

function unbanip() {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$ip = mysql_escape_string($_REQUEST['ip']);

	mysql_query("DELETE FROM {$COM_CONF['dbbannedipstable']} WHERE ip='$ip'", $comments_db_link);

	header("HTTP/1.1 302");
	header("Location: {$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=bannedlist");


}

function mark_as_not_junk () {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	$j_id = mysql_escape_string($_REQUEST['id']);

	$result = mysql_query("SELECT time, href, text, author, email, dont_show_email, ip FROM {$COM_CONF['dbjunktable']} WHERE id=$j_id", $comments_db_link);

	list ($time, $href, $text, $author, $email, $dont_show_email, $ip) = mysql_fetch_row($result);
	mysql_query("INSERT INTO {$COM_CONF['dbmaintable']} VALUES (NULL, '$time', '$href', '$text', '$author', '$email', '$dont_show_email', '$ip')", $comments_db_link);

	if (!mysql_error($comments_db_link)) {
		mysql_query ("DELETE FROM {$COM_CONF['dbjunktable']} WHERE id=$j_id", $comments_db_link);
		header("HTTP/1.1 302");
		header("Location: {$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=junk_list");
		print "<a href=\"{$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=junk_list\">Click here to get back.</a>";
	}
	else {
		echo mysql_error($comments_db_link);
	}


}

function clear_junk () {

	global $comments_db_link, $COM_CONF, $COM_LANG;

	mysql_query ("DELETE FROM {$COM_CONF['dbjunktable']} WHERE 1", $comments_db_link);

	header("HTTP/1.1 302");
	header("Location: {$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=junk_list");
	print "<a href=\"{$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}?action=junk_list\">Click here to get back.</a>";

}

function logout() {

	global $comments_db_link, $COM_CONF;

	setcookie("login", "", time()-999999, "{$COM_CONF['script_dir']}/");
	setcookie("passw", "", time()-999999, "{$COM_CONF['script_dir']}/");

	mysql_query("OPTIMIZE TABLE {$COM_CONF['dbbannedipstable']}");
	mysql_query("OPTIMIZE TABLE {$COM_CONF['dbmaintable']}");
	mysql_query("OPTIMIZE TABLE {$COM_CONF['dbemailstable']}");
	mysql_query("OPTIMIZE TABLE {$COM_CONF['dbjunktable']}");

	header("HTTP/1.1 302");
	header("Location: {$COM_CONF['site_url']}{$COM_CONF['admin_script_url']}");


}

?>
