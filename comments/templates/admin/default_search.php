<?

print<<<EOF
<html>
<head>
<title>List of pages</title>
<style type="text/css">
div#searchbox {
	background-color: #e0e0e0;
	color: inherit;
	padding: 10px;
}
</style>
<head>
<body>
EOF;
require ("./templates/admin/nav.php");
print<<<EOF
<div id="searchbox">
 <form method=GET action='{$COM_CONF['admin_script_url']}'>
  <b>Part of URL:</b> <input type="text" name="query" size="30" value="$query"> <input type="hidden" name="action" value="search"> <input type="submit" value="Search">
 </form>
</div>
<p>
EOF;
 
 if ($hrefs_count) {
  for($i=0; $i<$hrefs_count; $i++) {
	print "<a href=\"{$COM_CONF['admin_script_url']}?action=list&href=" . urlencode($href[$i]) . "\">{$href[$i]}</a> ({$count[$i]})<br>";
  }
 }
 else {
  	print "Your search did not match any pages with comments";
 }

print<<<EOF
    <p>$pages_string</p>
EOF;
require ("./templates/admin/nav.php");
print<<<EOF
</body>
</html>
EOF;


?>