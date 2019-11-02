<?php

include "finance.inc.php";

session_start();

$page = new page;
$page->title = "Personal Finances";

if($_SESSION['table'] && $_SESSION['table']->name == "account") {
	$table = $_SESSION['table'];
	$table->connect();
} else {
	$table = new table("finance");
	$table->order = "Account";
}

if(isset($_POST['balance'])) {
	$table->sql  = "SELECT DATE(table_a.Date) as Date, table_a.Description, ";
	$table->sql .= "table_a.Amount, table_a.Category, table_a.Account, ";
	$table->sql .= "sum(table_b.Amount) as Balance ";
	$table->sql .= "FROM transactions AS table_a ";
	$table->sql .= "LEFT JOIN transactions AS table_b ON table_a.id >= table_b.id ";
	$table->group = " GROUP BY table_a.id";
	$table->order = "table_a.id";
} else {
	$table->sql  = "SELECT Account, sum(Amount) as Balance ";
	$table->sql .= "FROM transactions ";
	$table->group = " GROUP BY Account WITH ROLLUP";
	$table->order = "";
}

$table->handle_get();

if(isset($_POST) && !empty($_POST)) {
	if(isset($_POST['findall'])) {
		$table->where = "";
		$table->page = 0;
	} else {
		foreach($_POST as $k => $v) {
			if($k != "submit" && $k != "balance") {
				if(isset($_POST['balance'])) {
					$a_where .= "table_a." . $k . " = '" . $v . "', ";
					$b_where .= "table_b." . $k . " = '" . $v . "', ";
				} else {
					$where .= $k . " = '" . $v . "', ";
				}
			}
		}
		
		if(isset($_POST['balance'])) {
			$a_where = "(" . substr($a_where, 0, -2) . ")";
			$b_where = "(" . substr($b_where, 0, -2) . ")";
			$table->where = " WHERE " . $a_where . " AND " . $b_where;
		} else {
			$where = substr($where, 0, -2);
			$table->where = " WHERE " . $where;
		}
		
		$table->page = 0;
	}
}

$table->query();

echo $page->header();

?>

<form method='post' action='list.php'>

<select name='Account'>
<option value=''>Select Account</option>
<option value='Capital One Visa'>Capital One Visa</option>
<option value='Cash'>Cash</option>
<option value='Checking'>Checking</option>
<option value='Laundry'>Laundry</option>
</select>

<input type='submit' value='Submit' name='submit'>
<input type='submit' value='Find All' name='findall'>
<!--<input type='checkbox' name='balance'>Show running balance-->
</form>

<?php
echo $table->navigation();
echo $table->display();

echo $page->footer();

$_SESSION['table'] = $table;

?>