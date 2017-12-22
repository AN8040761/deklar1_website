Back (accesskey b)    Save (accesskey s)    	File: /htdocs/outputspec.php
 	Status: This file has not yet been saved

<html>

<head>
<link rel="stylesheet" href="decl1_style.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>

<h1>Клетка 1</h1>

<table>
  <tr>
    <th colspan="5">Указания за попълване</th>
  </tr>
  <tr>
    <td colspan="5">1. Код корекция Попълва се буква "К", когато: <br>
	- се подава декларация за коригиране на подадена декларация;<br>
	- са изплатени възнаграждения за минало време, върху които се дължат вноски за социалното и здравното осигуряване, 
	допълнителното задължително пенсионно осигуряване, Учителски пенсионен фонд, фонд "Гарантирани вземания на работниците и служителите".<br>
	В тези случаи, освен коригираните, се попълват и всички останали реквизити с данните от подадената преди това декларация.
	Когато за един и същ месец осигурителят е подал повече от една основна декларация за едно лице и се налага да се коригира една от декларациите,
	се подава първо една заличаваща декларация, след което се подават отново декларациите като основни, с коректните данни.
	С код корекция "К" не се коригират данните в т. 2, 3, 4 и 5 (т. 5.1 и 5.2).
	След приемането на коригираща декларация се подава само нова коригираща или заличаваща декларация. Попълва се буква "З",
	когато се подава декларация за заличаване на подадена вече декларация.
	В тези случаи се попълват т. 1, 2, 3, 4 и 5 (т. 5.1 и 5.2) от декларацията. 
	Когато за един и същ месец осигурителят е подал повече от една декларация за едно лице и се налага да се заличи една от декларациите, 
	първо се подава една заличаваща декларация и след това се подават отново декларациите с коректните данни като основни.</td>
  </tr>
  <tr>
    <th colspan="5">Приложима нормативна уредба</th>
  </tr>
  <tr>
    <td colspan="5"><a href=http://lex.bg/laws/ldoc/2135514771 target="_blank">Наредба Н8</a></td>
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
  <?
include("dbinfo.inc.php");
mysql_query("SET NAMES UTF8");
$query="SELECT * FROM Deklar1Arr where nomer=14";
$result=mysql_query($query);



mysql_close();




$nomer=mysql_result($result,$i,"nomer");
$pole=mysql_result($result,$i,"pole");
$tip=mysql_result($result,$i,"tip");
$razmer=mysql_result($result,$i,"razmer");
$stoinost=mysql_result($result,$i,"stoinost");


echo "<tr><td> $nomer </td><td> $pole</td><td>$tip</td><td>$razmer</td><td>$stoinost</td></tr>";


?>
</table>


<? require("/home/vol15_1/getweb.cf/getw_18839007/htdocs/comments/comments.php"); ?>


</body>
</html>

