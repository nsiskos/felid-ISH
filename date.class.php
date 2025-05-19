<?php
class date_pulldown {
	
	public $months_en = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		
	public $timestamp;
	
	public $start_year = 2016;
	public $now_year;
	public $select_year;
	public $end_year;
	
	public $now_month;
	public $select_month;
	
	public $now_day;
	public $select_day;
	
	public $now_hour;
	public $select_hour;
	
	public $now_minute;
	public $select_minute;
	
#	public $now_second;
#	public $select_second;
	
	public function __construct () {
				
		$year = date("Y");
		$month = date("m");
		$day = date("d"); 
		$hour = date("H"); 
		$min = date("i"); 
		$sec = date("s");
		
		$this->set_man_date($year, $month, $day, $hour, $min);
		
	}
		
	public function set_man_date ($year, $month, $day, $hour, $min) {
		
		if ( !is_numeric($year) || !is_numeric($month) || !is_numeric($day) || !is_numeric($hour) || !is_numeric($min) ) {
			return "invalid input";
		} else {
		
			$this->now_year = $year;
			$this->end_year = $year+3;
	
			$this->now_month = $month;
			$this->now_day = $day; 
			$this->now_hour = $hour; 
			$this->now_minute = $min; 
			
			$this->timestamp = mktime($hour, $min, 0, $month, $day, $year);
		}
		
	}
	
	public function load_compact_date ($input_date) {
		
		$len = strlen($input_date);

		settype($input_date, "string");
		
		$year = substr($input_date, 0, 4);
		$month = substr($input_date, 4, 2);
		$day = substr($input_date, 6, 2);
		
		if ($len > 8 && $len < 14) {
			$hour = substr($input_date, 8, 2);
			$min = substr($input_date, 10, 2);
		} else {
			$hour = "17";
			$min = "00";
		}
		
		$this->set_man_date($year, $month, $day, $hour, $min);
	}
	
	public function compact_output () {
		return $this->now_year.$this->now_month.$this->now_day.$this->now_hour.$this->now_minute;
	}
	
	public function format_out ($format_string = 'j M Y') {
		
		return date($format_string, $this->timestamp);
		
	}
	
	public function format_date ($input_date) {
		return substr($input_date, 0, 4)."-".substr($input_date, 4, 2)."-".substr($input_date, 6, 2);
	}
	
	function make_list ($start, $end, $step, $selected, $format, $sprintf_format= "%'.02d", $selected_text="selected=\"selected\"", $array=array("ZERP")) {
		
		// $start : the starting point
		// $end : the ending point
		// $step : increment step
		// $selected : the selected element, may be a number, within the selected range
		
		// $format contains the pro-string, that may contain % for the id within for
		// # to place the selected identifier
		// @ to add the %th element of the array. if array contains less elements it is starting again via 'internal' pointer
		// e.g. $format = "<option value=\"%\" #> % - @ </option>";
		
		// $sprintf_format holds possible sprintf format string
		// it is set auto to "%'.02d" in order to help handle months data
		// can be omitted when set to 0
		
		// $array, can be the string array to be displayed after %, let's say the months
		
		// $selected_text can be anything to be displayed along with the selected element
		
		
		$output = "";
		$selector = 0;
		$array_length = sizeof($array);
		$internal_pointer = 0;
		
		// argument_check
				
		if ( !is_numeric($start) || !is_numeric($end) || !is_numeric($step) ) {
			echo "<br />No valid arguments provided! Exit.<br />";
			die;
		}
		
		if ($start <= $end) {
			$begin = $start;
			$stop = $end;
		} elseif ($start > $end) {
			$begin = $end;
			$stop = $start;
		}
		
		if (is_numeric($selected)) {
			
			// perform a check to see if it is within specified range
			if (($selected>=$begin) and ($selected<=$stop)) {
				$selector = $selected;
			} else {
				$selector = $begin;
			}
			
		} // or it is not a number, but a string (?), then it should be contained inside $array!!!
		else {
			
			$found = "NO";
			
			for ($j=0;$j<$array_length;++$j) {
				if ($array[$j] == $selected) {
					$found = $j;
					break;
				} else {
					continue;
				}
			}
			
			if ($found === "NO") {
				$selector = $begin;
			} else {
				$selector = $found+1;
			}
			
		}
		
		for ($i=$begin; $i<=$stop; $i+=$step) {
			
			if ( is_string($sprintf_format) and $sprintf_format != "0" ) {
				$row_substitution = str_replace("%", sprintf($sprintf_format, $i), $format);
			} else {
				$row_substitution = str_replace("%", $i, $format);
			}
			
			
			
			// check selected
			if ( $i == $selector ) {
				$selected_const = $selected_text;
			} else {
				$selected_const = "";
			}
			
			$selected_subst = str_replace("#", $selected_const, $row_substitution);
			
			if (($array[0] == "ZERP") and ($array_length == 1) ) {
				
				$final_text = $selected_subst;
				
			} else {
				
				$array_const = $array[$internal_pointer++];
				
				$final_text = str_replace("@", $array_const, $selected_subst);
				
				if ($internal_pointer == $array_length) {
					$internal_pointer = 0;
				}
				
			}
			
			$output .= $final_text;
			
		}
		
		return $output;

	}
	

	public function select_year ($select_name="year", $selected_option=0) {
			
		if ($selected_option < $this->start_year || $selected_option > $this->end_year) {
			$selected_option = $this->now_year;
		}
			
		return "<select name=\"".$select_name."\">\n".$this->make_list($this->start_year, $this->end_year, 1, $selected_option, "<option value=\"%\" #>%</option>\n")."</select>\n";
		
	}
	
	public function select_month ($select_name="month", $selected_option=0) {
		
		if ($selected_option < 1 || $selected_option > 12) {
			$selected_option = $this->now_month;
		}

		$out = "";

		$out .= "<select name=\"".$select_name."\">\n";
		
		$out .= $this->make_list(1, 12, 1, $selected_option, "<option value=\"%\" #>% - @</option>\n", "%'.02d", "selected=\"selected\"", $this->months_en);
		
		$out .= "</select>\n";
		
		return $out;
		
	}
	
	public function select_day ($select_name="day", $selected_option=0) {
		
		if ($selected_option < 1 || $selected_option > 31) {
			$selected_option = $this->now_day;
		}
		
		$out = "";

		$out .= "<select name=\"".$select_name."\">\n";
		
		$out .= $this->make_list(1, 31, 1, $selected_option, "<option value=\"%\" #>%</option>\n", "%'.02d", "selected=\"selected\"");
		
/*		for ($i=1; $i<=31; ++$i) {
			if ($i == $selected_option) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$day = sprintf("%'.02d", $i);
			$out .= "<option value=\"".$day."\" ".$selected.">".$day."</option>\n";
			
		}
*/		
		$out .= "</select>\n";	

	
		return $out;
		
	}
	
	public function select_hour ($select_name="hour", $selected_option=24) {
		
		if ($selected_option < 0 || $selected_option > 23) {
			$selected_option = $this->now_hour;
		}
		
		$out = "";

		$out .= "<select name=\"".$select_name."\">\n";
		
		$out .= $this->make_list(0, 23, 1, $selected_option, "<option value=\"%\" #>%</option>\n", "%'.02d", "selected=\"selected\"");
		
		/*
		for ($i=0; $i<=23; ++$i) {
			if ($i == $selected_option) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$hour = sprintf("%'.02d", $i);
			$out .= "<option value=\"".$hour."\" ".$selected.">".$hour."</option>\n";
			
		} */
		
		$out .= "</select>\n";	

	
		return $out;
		
	}
	
	public function select_minute ($select_name="minute", $selected_option=60) {
		
		if ($selected_option < 0 || $selected_option > 59) {
			$selected_option = $this->now_minute;
		}
		
		$out = "";

		$out .= "<select name=\"".$select_name."\">\n";
		
		$out .= $this->make_list(0, 59, 1, $selected_option, "<option value=\"%\" #>%</option>\n", "%'.02d", "selected=\"selected\"");
		
/*		for ($i=0; $i<=59; ++$i) {
			if ($i == $selected_option) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$min = sprintf("%'.02d", $i);
			$out .= "<option value=\"".$min."\" ".$selected.">".$min."</option>\n";
			
		}
*/		
		$out .= "</select>\n";	

	
		return $out;
		
	}
	
}

?>