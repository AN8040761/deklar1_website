ScriptsMill Comments Script v1.06 ReadMe

Requirements:

-PHP 4 or PHP 5
-MySql database: Create a Mysql database on your server (UNLESS you already have one!)
 to store the necessary tables.

========================================================================
Installation instructions:

1. Unpack the script files to a directory on your hard disk.

2. Upload all the script files to a directory on your server
   eg: /comments

   Make sure you keep the exact file structure.

3. Open install.php in your browser and follow the instructions.
   In case of permissions problems create config.php manually and
   copy/paste content that install script will generate for you
   into it.

4. Edit config.php if needed.

========================================================================

========================================================================
Upgrading instructions for users of Comments Script v1.05

You don't really need to upgrade from 1.05 if it works for you.


Upgrading instructions for users of Comments Script v1.04

Replace your comments.php with the file from this package. Copy recapthca 
directory from this package if you like to use Recaptcha. Add the following 
lines to your config.php after $COM_CONF['check_for_spam'] = 1;

// Possible values '', 'recaptcha', 'akismet'
$COM_CONF['check_for_spam_method'] = "recaptcha";
$COM_CONF['recaptcha_publickey'] = 'YOUR_RECAPTCHA_PUBLIC_KEY';
$COM_CONF['recaptcha_privatekey'] = 'YOUR_RECAPTCHA_PRIVATE_KEY';

You should get the keys at https://www.google.com/recaptcha/admin/create

Update default template with the file from this package (templates/default.php).

========================================================================

========================================================================
How to use

To show the comments on a page copy and paste the following code:

If your site uses php:

 <?php require("/path/to/your/www-home/comments/comments.php"); ?>

                 OR

 <?php virtual("/comments/comments.php"); ?>
 (doesn't work on some server configurations)

 You can find out what is path to your www-home with code like that:
 <?php echo dirname(__FILE__); ?>

 Actually if you don't know paths on your server it is better use this code:
 <?php require(dirname(__FILE__) . "/comments/comments.php"); ?>

 DO NOT use this script like that: require("http://yoursite.com/comments/comments.php").
 In this case you'll get the same comments for all your pages.

If your site uses SSI:

 <!--#include virtual="/comments/comments.php" -->

If your site uses ASP:

 <!--#include file="/comments/comments.php" -->
 (not tested)

The script recognizes itself the page where it is included and displays comments for this page.

You can create your own templates in "./templates" folder or edit existing files to customize
appearance of the comments. You can find several language files in "./lang" folder and you can
create your own language file there. Don't forget to edit "config.php" in order to use your
tempalte or language file. Please send me your templates and language files to info@scriptsmill.com,
I will include them to the packadge.
========================================================================

For support and new versions please visit http://www.scriptsmill.com/
