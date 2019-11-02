<?php

include "finance.inc.php";

session_start();

$widget = new widget;

$page = new page;
$page->title = "Personal Finances";

if($_SESSION['table'] && $_SESSION['table']->name == "transactions") {
	$table = $_SESSION['table'];
	$table->connect();
} else {
	$table = new table("transactions");
	$table->order = "Date";
}

// $table->sql  = "SELECT CONCAT('<a href=\"detail.php?id=', id, '\">', id, '</a>') AS id, ";
$table->sql  = "SELECT id, ";
$table->sql .= "DATE(Date) AS Date, Description, Amount, Category, Account ";
$table->sql .= "FROM transactions";

if($_SESSION['totals'] && $_SESSION['totals']->name == "totals") {
	$totals = $_SESSION['totals'];
	$totals->connect();
} else {
	$totals = new totals("totals");
}

$totals->sql  = "SELECT '' AS id, '' AS Date, 'Totals for found records' AS Description, ";
$totals->sql .= "SUM(Amount) AS Amount, '' AS Category, '' AS Account ";
$totals->sql .= "FROM transactions ";

$table->handle_get();

if(isset($_POST) && !empty($_POST)) {

	if(isset($_POST['findall'])) {
		$table->where = "";
		$totals->where = "";
		$table->page = 0;
		$_SESSION['month'] = "";
		$_SESSION['account'] = "";
		$_SESSION['category'] = "";
	} else {
		foreach($_POST as $k => $v) {
			$_SESSION[$k] = $v;
			if ($k == "month" && $v != "") {
				$where .= "DATE_FORMAT(date, '%c') = '" . $v . "' AND ";
			} elseif ($k != "submit" && $v != "") {
				$where .= $k . " = '" . $v . "' AND ";
			}
		}
		
		$where = substr($where, 0, -5);
		if (strlen($where) > 0) {
			$table->where = " WHERE " . $where;
			$totals->where = " WHERE " . $where;
		} else {
			$table->where = "";
			$totals->where = "";
		}
		$table->page = 0;
	}
}

$table->query();
$totals->query();

echo $page->header();


?>

<p align='center'><strong><?=$table->status()?></strong></p>
<p align='center'><?=$table->navigation()?></p>

<form name='form1' method='post' action='list.php'>
<?=$widget->select("transactions","Account","account", $_SESSION['account'], "onChange=\"this.form.submit();\"")?>
<?=$widget->select("transactions","Category","category", $_SESSION['category'], "onChange=\"this.form.submit();\"")?>
<select name='month' onChange='this.form.submit()'>
<option value=''>Select Month</option>
<option value='1' <? if($_SESSION['month'] == 1) echo " SELECTED"?>>January</option>
<option value='2' <? if($_SESSION['month'] == 2) echo " SELECTED"?>>February</option>
<option value='3' <? if($_SESSION['month'] == 3) echo " SELECTED"?>>March</option>
<option value='4' <? if($_SESSION['month'] == 4) echo " SELECTED"?>>April</option>
<option value='5' <? if($_SESSION['month'] == 5) echo " SELECTED"?>>May</option>
<option value='6' <? if($_SESSION['month'] == 6) echo " SELECTED"?>>June</option>
<option value='7' <? if($_SESSION['month'] == 7) echo " SELECTED"?>>July</option>
<option value='8' <? if($_SESSION['month'] == 8) echo " SELECTED"?>>August</option>,
<option value='9' <? if($_SESSION['month'] == 9) echo " SELECTED"?>>September</option>
<option value='10' <? if($_SESSION['month'] == 10) echo " SELECTED"?>>October</option>
<option value='11' <? if($_SESSION['month'] == 11) echo " SELECTED"?>>November</option>
<option value='12' <? if($_SESSION['month'] == 12) echo " SELECTED"?>>December</option>
</select>
<input type='submit' value='Find All' name='findall'>
</form>

<?=$table->display();?>
<?=$totals->display();?>

<?php

echo $page->footer();

$_SESSION['table'] = $table;
$_SESSION['totals'] = $totals;

?>