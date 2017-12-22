<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="decl1_style.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base target="_blank">
</head>
 
<body>
 <?php
include("dbinfo.inc.php");
	mysql_query("SET NAMES UTF8");
        
	$query="SELECT * FROM Deklar1Arr where nomer=$nomer";
	$result=mysql_query($query);
	$zaglavie=mysql_result($result,0,"zaglavie");
	$ukazaniya=mysql_result($result,0,"ukazaniya");
	$normativi=mysql_result($result,0,"normativi");
	$pole=mysql_result($result,0,"pole");
	$tip=mysql_result($result,0,"tip");
	$razmer=mysql_result($result,0,"razmer");
	$stoinost=mysql_result($result,0,"stoinost");
	
	mysql_close();?>


<table>
  <tr>
    <th colspan="5"><h1><?php echo $zaglavie; ?></h1></th>
  </tr>
  <tr>
    <td colspan="5">Представлява поле от Декларация образец № 1 "Данни за осигуреното лице" - Приложение № 1 към чл. 2, ал. 1 от Наредба № Н-8 на МФ от 29 декември 2005 г. За съдържанието, сроковете, начина и реда за подаване и съхранение на данни от работодателите, осигурителите за осигурените при тях лица, както и от самоосигуряващите се лица.</td>
  </tr>
  <tr>
    <th colspan="5">Указания за попълване</th>
  </tr>
  <tr>
    <td colspan="5"><?php echo $ukazaniya; ?></td>
  </tr>
  <tr>
    <th colspan="5">Приложима нормативна уредба</th>
  </tr>
  <tr>
    <td colspan="5"><?php echo $normativi; ?></td>
  </tr>
  <tr>
    <th colspan="5">Спецификация на съответната позиция във файла EMPL2000</th>
  </tr>
  <tr>
    <td>No.</td>
	<td>ПОЛЕ</td>
	<td>ТИП</td>
	<td>РАЗМЕР</td>
	<td>СТОЙНОСТ</td>
  </tr>

	
	<?php echo "<tr><td> $nomer </td>	<td> $pole</td>	<td>$tip</td><td>$razmer</td><td>$stoinost</td> </tr>"; ?>
  <tr>
    <td colspan="5" style="text-align: center;">
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
</td>
  </tr>
</table>
<div>
<? require("/home/vol15_1/getweb.cf/getw_18839007/htdocs/comments/comments.php"); ?>
</div>
<?php include('footer.php');?>
</body>
</html>

				