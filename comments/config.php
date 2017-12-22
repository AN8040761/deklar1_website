<?
$COM_CONF['site_url'] = "http://deklar1.getweb.cf";  // Without trailing slash

$COM_CONF['dbhost'] = "sql204.getweb.cf";
$COM_CONF['dbuser']="getw_18839007";
$COM_CONF['dbpassword']="HVFKOXKBH84jmYJD";
$COM_CONF['dbname']="getw_18839007_deklar1";
$COM_CONF['dbtablespreffix'] = "comments_";
$COM_CONF['dbmaintable'] = "{$COM_CONF['dbtablespreffix']}data";
$COM_CONF['dbemailstable'] = "{$COM_CONF['dbtablespreffix']}subscribes";
$COM_CONF['dbbannedipstable'] = "{$COM_CONF['dbtablespreffix']}banned";
$COM_CONF['dbjunktable'] = "{$COM_CONF['dbtablespreffix']}junk";

$COM_CONF['script_dir'] = "/comments";
$COM_CONF['admin_name'] = "deklar1";
$COM_CONF['admin_passw'] = "HVFKOXKBH84jmYJD";
$COM_CONF['email_admin'] = "alnastev@abv.bg";
$COM_CONF['email_from'] = "alnastev@abv.bg";
$COM_CONF['admin_script_url']="{$COM_CONF['script_dir']}/admin.php";

$COM_CONF['script_url']="{$COM_CONF['script_dir']}/comments.php";
$COM_CONF['template']="default";
$COM_CONF['lang']="bgr";
$COM_CONF['sort_order']="";      // If you want newest comments at the beginig use "desc"
                 // otherwise leave blank

$COM_CONF['anti_flood_pause'] = '60';  // in seconds

$COM_CONF['akismet_apikey'] = "";
$COM_CONF['check_for_spam'] = 1;

// Possible values '', 'recaptcha', 'akismet'
$COM_CONF['check_for_spam_method'] = "recaptcha";
$COM_CONF['recaptcha_publickey'] = "6LcD3ikTAAAAAGojVUj-60kQ7OwhqpuKaGRXpyoc";
$COM_CONF['recaptcha_privatekey'] = "6LcD3ikTAAAAADYMxRcSgncs5BYpg00nWyRZVZcn";

$COM_CONF['copy_random_seed'] = "IbGfDmrO9t"; // Was generated during install.
                         // Using in email notifications for unsubscribing.
                         // Don't change it!
?>