<?php

class page {
	var $title;
	var $style = "/finance/finance.css";
	
	function header() {
		$output  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\r";
		$output .= "<html>\r";
		$output .= "<head>\r";
		$output .= "<title>" . $this->title . "</title>\r";
		$output .= "<link rel=\"stylesheet\" href=\"http://" . $_SERVER["SERVER_NAME"] . $this->style . "\" />\r";
		$output .= "</head>\r";
		$output .= "<body>\r\r";

		return($output);
	}
	
	function footer() {
		$output  = "</body>\r";
		$output .= "</html>\r";
		
		return($output);
	}
}

class widget {
	var $db;
	
	function widget() {
		$this->db = new database;
		$this->db->connect();
	}
	
	function select($table, $field, $name, $value, $script="") {
		
		$sql = "SELECT DISTINCT " . $field . " FROM " . $table . " ORDER BY " . $field;
		
		$result = $this->db->query($sql);
		
		$output  = "<select name='" . $name . "'";
		if($script != "") {
			$output .= " " . $script;
		}
		$output .= ">\r";
		$output .= "<option value=''>Select " . $field . "</option>\r";
		
		while ($row = mysql_fetch_assoc($result)) {
			$output .= "<option value='" . $row[$field] . "'";
			if ($row[$field] == $value) {
				$output .= " selected";
			}
			$output .= ">" . $row[$field] . "</option>\r";
		}
		
		$output .= "</select>";
		
		return ($output);
	}
}

class database {
	var $host = "127.0.0.1";
	var $user = "sph";
	var $password = "vesuv1us";
	var $db = "finance";
	var $link;
	
	function database() {
		$this->connect();
	}

	function connect() {
		$this->link = mysql_connect($this->host, $this->user, $this->password);
		mysql_select_db($this->db);
	}
	
	function query($sql) {
		$result = mysql_query($sql, $this->link) or die('<p>Query failed: ' . mysql_error() . '</p><p>'. $sql . '</p>');
		return($result);
	}
}

class table {
	var $name;
	var $db;
	var $result;
	var $count;
	var $sql;
	var $where;
	var $order;
	var $reverse;
	var $limit = 20;
	var $page = 0;
	
	function table($name) {
		$this->name = $name;
		$this->connect();
	}
	
	function connect() {
		$this->db = new database;
		$this->db->connect();
	}
	
	function query() {

		$sql .= $this->sql . $this->where;
		
		$this->result = $this->db->query($sql);
		$this->count = mysql_num_rows($this->result);

		if(isset($this->order) && $this->order != "") {
			$sql .= " ORDER BY " . $this->order;
		}
		
		if($this->reverse == 1) {
			$sql .= " DESC ";
		}
		
		$sql .= " LIMIT " . ($this->page * $this->limit) . ", " . $this->limit;
		// echo "<p id=\"sql\">" . $sql . "</p>";
		$this->result = $this->db->query($sql);
	}
	
	function header() {
		$output .= "<tr id=\"row1\">";
		
		while ($i < mysql_num_fields($this->result)) {
			$field = mysql_fetch_field($this->result, $i);
			$field->name_rep = str_replace("_", " ", $field->name);
			$output .= "<td class=\"" . $this->name . "\" id=\"" . $this->name . "_" . $field->name . "\">\r";
			$output .= "<a href='list.php?o=" . $field->name;
			if($this->reverse == 0 && $field->name == $this->order) {
				$output .= "&r=1";
			} else {
				$output .= "&r=0";
			}
			$output .= "'>";
			$output .= $field->name_rep . "</a>";
			if($field->name == $this->order) {
				if($this->reverse == 0) {
					$output .= "&darr;";
				} else {
					$output .= "&uarr;";
				}
			}
			$output .= "\r\r</td>";
			$i++;
		}
		
		$output .= "</tr>";
		
		return($output);
	
	}

	function display() {
		
		$output  = "\r<table id=\"" . $this->name . "\">\r";
		
		$output .= $this->header();
		
		$i = 0;
		
		while ($row = mysql_fetch_assoc($this->result)) {
			$output .= "<tr id=\"" . $this->name . "_" . $i . "\">";
			foreach ($row as $k => $v) {
				$output .= "<td class=\"" . $this->name . "\" id=\"" . $this->name . "_" . $k . "\">\r" . $v . "\r\r</td>";
			}
			$output .= "</tr>";
			
			$i ++;
			$i = $i % 2;
		}
		
		$output .= "</table>";
		
		return ($output);
	}
	
	function handle_get() {
		if(isset($_GET['p'])) {
			$this->page = $_GET['p'];
		}
		
		if(isset($_GET['o'])) {
			$this->order = $_GET['o'];
			$this->reverse = 0;
			$this->page = 0;
		}
		
		if(isset($_GET['r'])) {
			$this->reverse = $_GET['r'];
		}
	}
	
	function navigation() {
		if ($this->page > 0) {
			$output .= "<a href='list.php?p=0'><< First</a>&nbsp;";
			$output .= "<a href='list.php?p=" . ($this->page - 1) . "'>";
			$output .= "< Prev</a>&nbsp;";
		} else {
			$output .= "<< First&nbsp;< Prev&nbsp;";
		}
		
		if (($this->page + 1) * $this->limit >= $this->count) {
			$output .= "&nbsp;Next >&nbsp;Last >>";
		} else {
			$last = ($this->count - ($this->count % $this->limit)) / $this->limit;
			$output .= "&nbsp;<a href='list.php?p=" . ($this->page + 1) . "'>";
			$output .= "Next ></a>";
			$output .= "&nbsp;<a href='list.php?p=" . $last . "'>Last >></a>";
		}
		
		return($output);
	}
	
	function status() {
		$sql  = $this->sql;
		
		$result = $this->db->query($sql);
		
		$count = mysql_num_rows($result);
		$last = ($this->count - ($this->count % $this->limit)) / $this->limit + 1;
		
		if ($count['Total'] == $this->count) {
			$output = "Displaying ". $count . " records, page " . ($this->page + 1) . " of " . $last;
		} else {
			$output  = "Displaying " . $this->count . " of " . $count . " records, page " . ($this->page + 1) . " of " . $last;
		}
		
		return($output);
	}
}

class totals extends table {
	function header() {
		$output .= "";
		
		return($output);
	
	}
}
?>