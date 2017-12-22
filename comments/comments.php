<?php

/*

Scriptsmill comments script v1.06

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


$COM_CONF['full_path'] = dirname(__FILE__);

include("{$COM_CONF['full_path']}/config.php");
if (! $COM_CONF['dbhost']) {
    echo 'It seems that comments script is not properly installed. See readme.txt for more info.';
}

require("{$COM_CONF['full_path']}/lang/lang_{$COM_CONF['lang']}.php");

$comments_db_link = mysql_connect($COM_CONF['dbhost'],$COM_CONF['dbuser'],$COM_CONF['dbpassword']);
mysql_select_db($COM_CONF['dbname'], $comments_db_link);

define('SPAMCHECK_OK', 0);
define('SPAMCHECK_IS_SPAM', 1);
define('SPAMCHECK_API_KEY_ERROR', 2);
define('SPAMCHECK_CONNECTION_PROBLEM', 3);
define('SPAMCHECK_CAPTCHA_ERROR', 4);

smcom_main();

function smcom_main()
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add'){
        smcom_add();
    }
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'unsub'){
        smcom_unsub();
    }
    elseif (1) {
        smcom_view();
    }
}

function smcom_check_for_errors() {

    global $comments_db_link, $COM_CONF, $COM_LANG;

    $ip = mysql_escape_string($_SERVER['REMOTE_ADDR']);
    $result = mysql_query("SELECT ip FROM {$COM_CONF['dbbannedipstable']} WHERE ip='$ip'", $comments_db_link);

    $error_message = "";
    if (mysql_num_rows($result)>0) {
        $error_message.=$COM_LANG['not_allowed'] . "<br />";
    }
    if ($_REQUEST['disc_name'] == '') {
        $error_message.=$_REQUEST['r_disc_name'] . "<br />";
    }
    if ($_REQUEST['disc_body'] == '') {
        $error_message.=$_REQUEST['r_disc_body'] . "<br />";
    }
    if ($_REQUEST['disc_email'] != '') {
        if (!smcom_is_email($_REQUEST['disc_email'])) {
            $error_message.="Invalid email address" . "<br />";
        }
    }

    return $error_message;

}

function smcom_flood_protection($INPUT) {

    global $comments_db_link, $COM_CONF, $COM_LANG;

    $result = mysql_query("SELECT time FROM {$COM_CONF['dbmaintable']}
        WHERE ip='{$_SERVER['REMOTE_ADDR']}' AND  (UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP( time )) < {$COM_CONF['anti_flood_pause']}", $comments_db_link);
    if (mysql_num_rows($result)>0) {
        $error_message="Flood detected";
        return $error_message;
    }
    $result = mysql_query("SELECT ID FROM {$COM_CONF['dbmaintable']}
        WHERE text='{$INPUT['disc_body']}' AND author='{$INPUT['disc_name']}' AND href='{$INPUT['href']}'", $comments_db_link);
    if (mysql_num_rows($result)>0) {
        $error_message="Flood detected";
        return $error_message;
    }

    return "";
}

function smcom_spam_check_recaptcha()
{
    global $COM_CONF;

    if (empty($_POST["recaptcha_challenge_field"]) || empty($_POST["recaptcha_response_field"])) {
        return SPAMCHECK_CAPTCHA_ERROR;
    }

    require_once($COM_CONF['full_path'] . '/recaptcha/recaptchalib.php');
    $resp = recaptcha_check_answer ($COM_CONF['recaptcha_privatekey'],
        $_SERVER["REMOTE_ADDR"],
        $_POST["recaptcha_challenge_field"],
        $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
        if ($resp->error != "incorrect-captcha-sol") {
            echo "<br>" . $resp->error;
        }
        return SPAMCHECK_CAPTCHA_ERROR;
    }
    else {
        return SPAMCHECK_OK;
    }
}

function smcom_spam_check_akismet($email, $name, $url, $text, $path_to_page, $ip) {

    global $COM_CONF, $comments_db_link;
    require("{$COM_CONF['full_path']}/akismet-class.php");

    $try = 0;
    while (!$valid && $try <= 3) {
        // Initialize and verify API key
        $akismet = new Akismet($COM_CONF['site_url'], $COM_CONF['akismet_apikey']);
        $result = $akismet->isKeyValid();
        // Possible values: 'valid', 'invalid', 'no connect'
        if ($result != 'valid') {
            if (($result == 'invalid')) {
                // Invalid key
                return SPAMCHECK_API_KEY_ERROR;
            } else {
                // Could not connect to the Akismet server
                $try++;
            }
        }
        else {
            $valid = 1;
        }
    }

    if (!$valid) {
        return SPAMCHECK_CONNECTION_PROBLEM; // Could not connect to the Akismet server
    }

    // Pass comment info to the class
    $akismet->setCommentAuthorEmail($email);
    $akismet->setCommentAuthor($name);
    $akismet->setCommentAuthorURL($url);
    $akismet->setCommentContent($text);
    $akismet->setUserIP($ip);
    $akismet->setPermalink($COM_CONF['site_url'] . $path_to_page);
    $akismet->setCommentType('Comment');

    $try = 0;
    while ($try <= 3) {
        // Check the comment for spam
        $result = $akismet->isCommentSpam();
        // Possible values: 'false' (not spam), 'true' (spam), 'no connect'
        if ($result != 'false') {
            if ($result == 'true') {
                // The comment is spam
                return SPAMCHECK_IS_SPAM;
            } else {
                // Could not connect to the Akismet server
                $try++;
            }
        } else {
            return SPAMCHECK_OK;
        }
    }

    return SPAMCHECK_CONNECTION_PROBLEM; // Could not connect to the Akismet server
}

function smcom_add()
{
    global $comments_db_link, $COM_CONF, $COM_LANG;

    foreach ($_REQUEST as $key => $value) {
        if ($key == 'disc_body') {
            $comment_text=stripslashes($value);
        }
        $_REQUEST[$key] = str_replace('<', '&lt;', $_REQUEST[$key]);
        $_REQUEST[$key] = str_replace('>', '&gt;', $_REQUEST[$key]);
        if (get_magic_quotes_gpc()) {
            $_REQUEST[$key] = stripslashes($_REQUEST[$key]);
        }
        $_REQUEST[$key] = mysql_escape_string($_REQUEST[$key]);
    }

    $_REQUEST['href'] = str_replace('%2F', '/', $_REQUEST['href']);
    $_REQUEST['href'] = str_replace('%3F', '?', $_REQUEST['href']);
    $_REQUEST['href'] = str_replace('%26', '&', $_REQUEST['href']);
    $_REQUEST['href'] = str_replace('%3D', '=', $_REQUEST['href']);

    if ($_REQUEST['dont_show_email'] != '') $dont_show="1";
    else $dont_show="0";

    $error_message = smcom_check_for_errors();
    $error_message .= smcom_flood_protection($_REQUEST);

    if ($COM_CONF['check_for_spam']) {
        if (!$error_message) {
            if ($COM_CONF['check_for_spam_method'] == 'akismet') {
                $spam_check_result = smcom_spam_check_akismet($_REQUEST['disc_email'], $_REQUEST['disc_name'], "", $comment_text, $_REQUEST['href'], "");
            }
            elseif ($COM_CONF['check_for_spam_method'] == 'recaptcha') {
                $spam_check_result = smcom_spam_check_recaptcha();
            }
            elseif (true) {
                $error_message .= "<br>Config error, spam checking enabled, but checking method is not defined.";
                $spam_check_result = 0;
            }
            if ($spam_check_result == SPAMCHECK_IS_SPAM) {
                $error_message .= "<br>Your comment suspected as spam.";

                mysql_query("INSERT INTO {$COM_CONF['dbjunktable']}
                    VALUES (NULL, NOW(), '{$_REQUEST['href']}', '{$_REQUEST['disc_body']}', '{$_REQUEST['disc_name']}',
                        '{$_REQUEST['disc_email']}', '$dont_show', '{$_SERVER['REMOTE_ADDR']}')", $comments_db_link);
            }
            if ($spam_check_result == SPAMCHECK_API_KEY_ERROR) {
                $error_message .= "<br>Invalid API key";
            }
            if ($spam_check_result == SPAMCHECK_CONNECTION_PROBLEM) {
                $error_message .= "<br>Could not connect to spam checking server";
            }
            if ($spam_check_result == SPAMCHECK_CAPTCHA_ERROR) {
                $error_message .= "<br>You've typed wrong letters";
            }
        }
    }


    if (!empty($error_message)) {
        print "The following errors occured:<br>$error_message<br><br>
            Please <a href=\"javascript:history.go(-1)\">get back</a> and try again.";
        return 0;
    }


    mysql_query("INSERT INTO {$COM_CONF['dbmaintable']}
        VALUES (NULL, NOW(), '{$_REQUEST['href']}', '{$_REQUEST['disc_body']}', '{$_REQUEST['disc_name']}',
        '{$_REQUEST['disc_email']}', '$dont_show', '{$_SERVER['REMOTE_ADDR']}')", $comments_db_link);

    if ($_REQUEST['email_me'] != '' && $_REQUEST['disc_email'] != '') {
        $result = mysql_query("select COUNT(*) from {$COM_CONF['dbemailstable']} where href='{$_REQUEST['href']}' AND email='{$_REQUEST['disc_email']}'", $comments_db_link);
        list ($count) = mysql_fetch_row($result);
        if ($count == 0) {
            $hash=md5($email . $COM_CONF['copy_random_seed']);
            mysql_query("INSERT INTO {$COM_CONF['dbemailstable']} VALUES (NULL, '{$_REQUEST['disc_email']}', '{$_REQUEST['href']}', '$hash')", $comments_db_link);
        }
    }

    if ($COM_CONF['email_admin']) {
        smcom_notify_admin($_REQUEST['href'], $_REQUEST['disc_name'], $_REQUEST['disc_email'], $comment_text, "{$_SERVER['REMOTE_ADDR']}, {$_SERVER['HTTP_USER_AGENT']}");
    }
    smcom_notify_users($_REQUEST['href'], $_REQUEST['disc_name'], $_REQUEST['disc_email']);

    header("HTTP/1.1 302");
    header("Location: {$COM_CONF['site_url']}{$_REQUEST['href']}");
    print "<a href=\"{$COM_CONF['site_url']}{$_REQUEST['href']}\">Click here to get back.</a>";

}

function smcom_notify_admin($href, $name, $email, $text, $ip)
{
    global $comments_db_link, $COM_CONF, $COM_LANG;

    $headers = "From: Comments <{$COM_CONF['email_from']}>\r\n";
    $text_of_message="
{$COM_LANG['email_new_comment']} {$COM_CONF['site_url']}$href
{$COM_LANG['email_from']}: $name <$email>

$text

$ip
";

mail($COM_CONF['email_admin'], "{$COM_LANG['email_new_comment']} $href", $text_of_message, $headers);
}

function smcom_notify_users($href, $name, $email_from)
{
    global $comments_db_link, $COM_CONF, $COM_LANG;

    $headers = "From: Comments <{$COM_CONF['email_from']}>\n";

    $result=mysql_query("select email, hash from {$COM_CONF['dbemailstable']} where href='$href'", $comments_db_link);
    while (list($email, $hash) = mysql_fetch_row($result)) {
        if ($email != $email_from) {
            $text_of_message="
        {$COM_LANG['email_new_comment']} {$COM_CONF['site_url']}$href
        {$COM_LANG['email_from']}: $name

        {$COM_LANG['email_to_unsubscribe']}
        {$COM_CONF['site_url']}{$COM_CONF['script_url']}?action=unsub&page=$href&id=$hash

        ";
            mail($email, "{$COM_LANG['email_new_comment']} $href",$text_of_message, $headers);
        }
    }


}

function smcom_unsub()
{
    global $comments_db_link, $COM_CONF, $COM_LANG;

    $id=mysql_escape_string($_REQUEST['id']);
    $href=mysql_escape_string($_REQUEST['page']);

    mysql_query("delete from {$COM_CONF['dbemailstable']} where href='$href' AND hash='$id'", $comments_db_link);

    if (mysql_affected_rows() > 0) {
        print "{$COM_LANG['unsubscribed']}";
    }
    else {
        print "{$COM_LANG['not_unsubscribed']}";
    }
}

function smcom_view()
{
    global $comments_db_link, $COM_CONF, $COM_LANG;

    $request_uri = mysql_escape_string($_SERVER['REQUEST_URI']);
    $result = mysql_query("SELECT time, text, author, email, dont_show_email FROM {$COM_CONF['dbmaintable']}
                           WHERE href='$request_uri' order by time {$COM_CONF['sort_order']}", $comments_db_link);

    $comments_count=0;
    $time=$text=$author=$email=$dont_show_email=array();
    while (list($time[$comments_count], $text[$comments_count], $author[$comments_count], $email[$comments_count], $dont_show_email[$comments_count])=mysql_fetch_array($result)) {
        $text[$comments_count] = wordwrap($text[$comments_count], 75, "\n", 1);
        $time[$comments_count] = smcom_format_date($time[$comments_count]);
        $comments_count++;
    }

    require("{$COM_CONF['full_path']}/templates/{$COM_CONF['template']}.php");

}

function smcom_format_date($date)
{
    global $COM_LANG;

    $year = substr($date, 0, 4);
    $month = intval(substr($date, 5, 2)) - 1;
    $day = substr($date, 8, 2);
    $hour = substr($date, 11, 2);
    $min = substr($date, 14, 2);

    return "$day {$COM_LANG['months'][$month]} $year, $hour:$min";
}

function smcom_is_email($Addr)
{
    $p = '/^[a-z0-9!#$%&*+-=?^_`{|}~]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*';
    $p.= '@([-a-z0-9]+\.)+([a-z]{2,3}';
    $p.= '|info|arpa|aero|coop|name|museum)$/ix';
    return preg_match($p, $Addr);
}

?>
