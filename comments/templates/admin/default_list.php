<?

print<<<EOF
<html>
<head>
<title>Comments on $request_uri</title>
</head>
<body>
EOF;
require ("./templates/admin/nav.php");
print<<<EOF
<style type="text/css">
div#usernotes {
	background-color: #e0e0e0;
	color: inherit;
}
div#usernotes div.head, div#usernotes div.foot {
	background-color: #d0d0d0;
	color: inherit;
	padding: 4px;
}
div#usernotes div.foot {
	text-align: right;
}
div#usernotes div.foot a, div#usernotes div.head a {
	color: black;
	background-color: transparent;
}
div#usernotes span.action {
	float: right;
}
div#usernotes div.note {
	padding: 4px;
}
div#usernotes div.text {
	background-color: #f0f0f0;
	color: inherit;
	padding: 2px;
	margin-top: 4px;
}
</style>

<div id="usernotes">
 <div class="head">
 <H3>{$COM_LANG['header']}</H3>
 </div>
EOF;

 if ($comments_count) {
  for($i=0; $i<$comments_count; $i++) {
   if ($dont_show_email[$i] != '1' && $email != '') { $author[$i] = "<a href=\"mailto:{$email[$i]}\">{$author[$i]}</a>"; }
   $text[$i] = str_replace(chr(13), '<br />', $text[$i]);
   
   print<<<EOF
 <div class="note">
  <strong>{$author[$i]}</strong> <i>({$ip[$i]})</i><br />
  <small>{$time[$i]}</small>
  <span class="action"><a href="{$COM_CONF['admin_script_url']}?action=delete&id={$id[$i]}&from={$_REQUEST['href']}">[Delete]</a> <a href="{$COM_CONF['admin_script_url']}?action=banip&ip={$ip[$i]}&from={$_REQUEST['href']}">[Ban IP]</a></span>
  <div class="text">
  {$text[$i]}
  </div>
 </div>
EOF;
  
  }
 }
 else {
   print<<<EOF
 <div class="note">
  <div class="text">
  {$COM_LANG['no_comments_yet']}
  </div>
 </div>
EOF;
 }
 
print<<<EOF
</div>
    <p>$pages_string</p>
EOF;
require ("./templates/admin/nav.php");
print<<<EOF
</body>
</html>
EOF;

?>