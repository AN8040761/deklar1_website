<?

print<<<EOF
<html>
<body>
<head>
<title>{$COM_LANG['admin_panel_name']} | Login</title>
</head>
<h1>{$COM_LANG['admin_panel_name']}</h1>
<form action="{$COM_CONF['admin_script_url']}" METHOD=POST>
<table>
 <TR>
  <TD align='right' valign='top'>Login:</TD>
  <TD><input type='text' name='login'></TD>
 </TR>
 <TR>
  <TD align='right' valign='top'>Password:</TD>
  <TD><input type='password' name='passw'></TD>
 </TR>
 <TR>
  <TD></TD>
  <TD><input type='submit' name='submit' value='Submit'></TD>
 </TR>
</table>
Cookies must be enabled past this point.<br>
<input type='hidden' name='action' value='login'>
</form>
</body>
</html>
EOF;

?>