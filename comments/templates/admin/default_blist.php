<?

print<<<EOF
<html>
<head>
<title>Banned IPs</title>
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
 <H3>Banned IPs</H3>
 </div>
EOF;

 if ($ips_count) {
  for($i=0; $i<$ips_count; $i++) {
   print<<<EOF
 <div class="note">
   <div class="text">
    {$ip[$i]}
    <a href="{$COM_CONF['admin_script_url']}?action=unbanip&ip={$ip[$i]}">[Unban IP]</a>
   </div>
 </div>
EOF;
  
  }
 }
 else {
   print<<<EOF
 <div class="note">
  <div class="text">
  There are no banned IPs.
  </div>
 </div>
EOF;
 }
 
print<<<EOF
</div>
EOF;
require ("./templates/admin/nav.php");
print<<<EOF
</body>
</html>
EOF;

?>