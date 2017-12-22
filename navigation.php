<?php
include("dbinfo.inc.php");
	mysql_query("SET NAMES UTF8");
        

         $query="SELECT * FROM PageNumber where nomer=$nomer";
	$result=mysql_query($query);
	$pnp=mysql_result($result,0,"poreden_nomer_pole");

        $query="SELECT * FROM PageNumber where poreden_nomer_pole=$pnp-1";
	$result=mysql_query($query);
	$predishna=mysql_result($result,0,"adres");
        
         $query="SELECT * FROM PageNumber where poreden_nomer_pole=$pnp+1";
	$result=mysql_query($query);
	$sledvashta=mysql_result($result,0,"adres");
	
	mysql_close();?>
<img src="navigation_black_text.jpg" alt="Web Logos" usemap="#navigation" />
<map name="navigation">

  <area shape="poly" coords="32,120,113,70,113,101,144,101,144,139,113,139,113,170" <? echo '<a href="' . $predishna . '" target=_self></a>'; ?>  
  <area shape="poly" coords="356,120,275,70,275,101,244,101,244,139,275,139,275,170" <? echo '<a href="' . $sledvashta . '" target=_self></a>'; ?>  
  <area shape="poly" coords="113,101,113,70,194,20,275,70,275,101" href="calculator-osigurovki.html"  target="_blank" />
  <area shape="poly" coords="113,139,113,170,194,220,275,170,275,139" href="links.html"  target="_blank" />
  <area shape="poly" coords="144,101,144,139,244,139,244,101" href="main.php"  target="_blank" />
</map>