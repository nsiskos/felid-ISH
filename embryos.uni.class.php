<?php

class dataset {
	
	public $db;
	
	public $dataset;
	public $dataJSON;
	
	public $dataJSONFlag;
	public $graphClassAbFooter;
	
	
	public function __construct($db) {
		$this->db = $db;
		$this->dataJSONFlag = 0;
		$this->graphClassAbFooter = 0;
		
		$this->dataJSON = 0;
		
		$sql = "select 
			section.slide_id,
			embryo.id as embryo_id,
			embryo.name as embryo_name,
			round(avg(section.rating),1) as ratingAVG, 
			gene.gene_name||'_'||gene.solution_book as GENE, 
			substr(slide.cut_date, 1, 4) ||'-'||substr(slide.cut_date, 5, 2)||'-'||substr(slide.cut_date, 7, 2) as CDATE, 
			substr(slide.experiment_date, 1, 4)||'-'||substr(slide.experiment_date, 5, 2)||'-'||substr(slide.experiment_date, 7, 2) AS EDAY 
		from section 
		inner join slide on slide.id=section.slide_id 
		inner join gene on slide.gene=gene.id
		inner join embryo on slide.embryo_id=embryo.id 
		group by slide_id
		";

		$date_ret = $db->prepare($sql);
		$date_ret->execute();

		$dataset = array();

		$dataset = $date_ret->fetchAll(PDO::FETCH_ASSOC);

		#echo "TEST: ".strtotime($dataSet[0]['CDATE']);


		array_walk($dataset, function(&$score, $index){
			$start = strtotime($score['CDATE']);
			$end = strtotime($score['EDAY']);
			if ($end >= $start) {
				$score['daysBetween'] = ($end-$start)/(24*60*60);
			} else {
				$score['daysBetween'] = "minus";
			}
		});

		#$extras_in_header .= "<script src=\"https://d3js.org/d3.v5.js\"></script>\n";
		
		$this->dataset = $dataset;
		$this->dataJSON = json_encode($this->dataset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);


		
	}
	

	public function plotGraph ($dom_element, $width, $height, $bubbleClass) {
		
		if ($this->dataJSONFlag) {
			$js_function = "<script>const chart2 = new scatterplot({data: ".$this->dataset_name.",	element: document.querySelector('#".$dom_element."'), width: ".$width.",	height: ".$height.", bubbleClass: '".$bubbleClass."'});</script>";
		} else {
			$js_function = "<script>const chart2 = new scatterplot({data: ".$this->dataJSON.",	element: document.querySelector('#".$dom_element."'), width: ".$width.",	height: ".$height.", bubbleClass: '".$bubbleClass."'});</script>";
		}
		
		
		
#		$js_function = "<script>const chart2 = new scatterplot({data: ".$dataset.",	element: document.querySelector('#".$dom_element."'), width: ".$width.",	height: ".$height.", bubbleClass: '".$bubbleClass."'});</script>";
		
		return $js_function;
		
	}
	
	public function datasetJS ($dataset_name) {
		
		$this->dataset_name = $dataset_name;
		
		if ($this->dataJSONFlag) {
			return 1;
		} else {
			$this->dataJSONFlag = 1;
			
			return "<script>var ".$dataset_name."=".$this->dataJSON.";</script>\n";
		}
	}
	
	public function loadGraphClassAbFooter () {
		
		if ($this->graphClassAbFooter) {
			return 1;
		} else {
			$this->graphClassAbFooter = 1;
			return "<script type=\"text/javascript\" src=\"statScript/ScoreScatter0.js\"></script>\n";
		}
		
		
	}
	
}

// this class finds the embryo by its name OR its id
class embryo {

	public $db;
	public $restriction_flag;
	public $embryo_id;
	public $embryo_name;
	public $embryo_data;
	/*
	** $embryo_data = array (id, madre_id, name, part, cut, age, comments, set_width, set_height, crl)
	*/
	
	public $cut_status; # cut or not?
	public $embryo_siblings;
	public $embryo_operations_array;
	public $embryo_operations_html;
	public $embryo_slides; # array with ZERP as 1st element
	public $sections_count = 0;
	public $embryo_pictures;
	public $group_picture;
	
	public $animal_rating = 0;
	public $slides_rated; # array of the slides bearing a rating
	public $failed_slides = 0;
	
	public $header_row_el = array("&nbsp;", "Α", "Β", "Γ", "Δ", "Ε", "ΣΤ", "Z", "Η", "Θ", "I", "ΙΑ", "ΙΒ", "ΙΓ", "ΙΔ");
	public $header_row_en = array("&nbsp;", "A", "B", "C", "D", "E",  "F", "G", "H", "I", "J", "K", "L", "M", "N");

	public function __construct($db, $embryo_ident, $restriction_flag="ON") {
		
		$this->db = $db;
		$this->restriction_flag = $restriction_flag;
		$check_embryo_ident = preg_match('/\d+\.\d+/', $embryo_ident);
		if ($check_embryo_ident) {

			$this->embryo_name = $embryo_ident;

			$embryo_selector = "select * from embryo where name=? and part!='body'";
			$embryo_prep = $db->prepare($embryo_selector);
			$embryo_prep->bindValue(1, $embryo_ident, PDO::PARAM_STR);
			$embryo_prep->execute();
		
			$fetched_data = $embryo_prep->fetch(PDO::FETCH_ASSOC);
			
			$this->embryo_data = $fetched_data;
			$this->embryo_id = $fetched_data['id'];
		} else if ( preg_match('/^[0-9]*$/', $embryo_ident) ) {

			$this->embryo_id = $embryo_ident;
		
			$embryo_selector = "select * from embryo where id=?";
			$embryo_prep = $db->prepare($embryo_selector);
			$embryo_prep->bindValue(1, $embryo_ident, PDO::PARAM_INT);
			$embryo_prep->execute();

			$this->embryo_data = $embryo_prep->fetch(PDO::FETCH_ASSOC);
			$this->embryo_name = $this->embryo_data['name'];
		} else {
			echo "Bad input! Failed to initialise!";
			echo "Input expected to be \\d+\\.\\d+ or integer but is: ".gettype($embryo_ident)." (".$embryo_ident.")";
			die;
		}
		
		$this->cut_status = $this->set_cut_status();
		
#		echo "<pre>";
#		print_r($this->embryo_data);
#		echo "</pre>";
		
	#	return $this->embryo_data;
		

	}
	
	private function set_cut_status ($width = -1, $height = -1) {
		
		if ( ($height < 0) or (!is_numeric($height)) or (!is_numeric($width)) ) {
			$height = (int)$this->embryo_data['set_height'];
			$width = (int)$this->embryo_data['set_width'];
		}
				
		if ( ($height>=1) && ($width>=1) ) {
			return "cut";
		} else {
			return "uncut";
		}
		
#		echo "<pre>";
#		echo $height." - ".gettype($height)."\n";
#		echo $width." - ".gettype($width)."\n";
#		echo "</pre>";
	}

	public function get_siblings () {
		
		$sql = "select id, name, part, substr(cut, 1, 3) as cut, set_height, set_width from embryo where madre_id = ? and id != ?";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $this->embryo_data['madre_id'], PDO::PARAM_INT);
		$prep->bindValue(2, $this->embryo_id, PDO::PARAM_INT);
		$prep->execute();
		$this->embryo_siblings = $prep->fetchAll(PDO::FETCH_ASSOC);

#		echo "<pre>";
#		echo $this->embryo_id."\n";
#		echo $this->embryo_data['madre_id'];
#		print_r($this->db->errorInfo());
#		print_r($result);
#		echo "</pre>";
	}
	
	public function print_siblings () {
		
		$output = "";
		
		foreach ($this->embryo_siblings as $sib) {
		
			$sib_link = $sib['name']." ".$sib['part'];
			
			$cut_status = $this->set_cut_status((int)$sib['set_height'], (int)$sib['set_width']);
		
			if ($cut_status == "cut") {
				$sib_str = " [".$sib['cut']."|".$sib['set_height']."x".$sib['set_width']."]";
			} else {
				$sib_str = " [uncut]";
			}
		
			$output .= "<li><a href=\"".FRONTPAGE."?opr=showem&name=".$sib['id']."\">".$sib_link."</a>".$sib_str;
		
		}
		
		return $output;
		
	}
	
	public function format_date ($input_date) {
		return substr($input_date, 0, 4)."-".substr($input_date, 4, 2)."-".substr($input_date, 6, 2);
	}
	
	public function get_embryo_picture () {
		$sql = "select * from embryo_photos where embryo_id = ?";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
		$prep->execute();
		$result = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($result as $photo) {
			$allPics[] = ["embryo", $photo['file_name'], $this->embryo_name, $photo['id'], $photo['embryo_pic_descr']];
		}
		
		if (count($result) < 1) {
			$this->embryo_pictures = "not_found";
		} else {
			$this->embryo_pictures = $allPics;
		}
		
#		$sql2 = "select file_name from group_photos where madre_id=(select madre_id from embryo where embryo.id=?)";
/*
		$sql2 = "select file_name from group_photos where madre_id=?";
		$prep2 = $this->db->prepare($sql2);
		$prep2->bindValue(1, $this->embryo_data['madre_id'], PDO::PARAM_INT);
		$prep2->execute();
		$res2 = $prep2->fetch(PDO::FETCH_ASSOC);
		$this->group_picture = $res2['file_name'];
*/		


	}
	
	public function relatedOperations () {
#		$operations_sql = "select name, when_date from operations where embryo_id = ".$this->embryo_id." order by when_date asc";
		$operations_sql = "select id, name, when_date from operations where embryo_id=? order by when_date asc";
		$prepare_op = $this->db->prepare($operations_sql);
		$prepare_op->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
		$prepare_op->execute();
		$this->embryo_operations_array = $prepare_op->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function lastOperation () {
		return $this->embryo_operations_array[count($this->embryo_operations_array)-1]['name'];
	}
	
	public function formatOperations () {
#		$text = "<br>Operations history:";
		$text = "";
		foreach ($this->embryo_operations_array as $operation) {
			$text .= $this->format_date($operation['when_date'])."&nbsp;:&nbsp;".$operation['name'];
			if ($this->restriction_flag == "OFF") {
				$text .= "&nbsp;<a href=\"recordare.php?action=mod_oper&oper_id=".$operation['id']."\">&raquo;&raquo;</a>";
			}
			$text .= "<br />";
		}
		$this->embryo_operations_html = $text;
	}
	
	public function relatedSlides () {
#		$get_slides_sql = "select name, slide_position, experiment_date, gene, status, cut_date from slide where embryo_id=?";
#		$get_slides_sql = "select slide.id, name, slide_position, experiment_date, gene.gene_name as gene, gene.colour as colour, status, cut_date from slide left outer join gene on gene.id=slide.gene where embryo_id=?";
#		$get_slides_sql = "select slide.id, name, slide_position, experiment_date, gene.gene_name as gene, gene.colour as colour, gene.solution_book as solBook, status, cut_date from slide left outer join gene on gene.id=slide.gene where embryo_id=?";
		
		$get_slides_sql = "select slide.id, name, slide_position, experiment_date, gene.gene_name as gene, gene.colour as colour, gene.solution_book as solBook, status, cut_date, (select count(section.id) from section where section.slide_id = slide.id) as section_count, (select avg(section.rating) from section where section.slide_id = slide.id) as section_rating from slide left outer join gene on gene.id=slide.gene where embryo_id=?";
		
		$slides_prep = $this->db->prepare($get_slides_sql);
		$slides_prep->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
		$slides_prep->execute();
		
		$slides_start = $slides_prep->fetchAll(PDO::FETCH_ASSOC);
		
		array_walk($slides_start, function (&$slide, $index) {
			settype($slide['id'], "int");
			settype($slide['solBook'], "int");
			settype($slide['section_count'], "int");
			settype($slide['section_rating'], "float");
		});
		
		$this->embryo_slides = array_merge(array("ZERP"), $slides_start);
		
		$this->sections_count = $this->how_many_sections();
		$this->failed_slides = $this->how_many_failed_slides();
#		$this->blank_slides = $this->how_many_blank_slides();
		
#		echo "<pre>";
#		var_dump($this->embryo_slides);
#		echo "</pre>";
	}
	
	private function how_many_failed_slides () {
		
		$failed = 0;
		foreach ($this->embryo_slides as $slide) {
			if (isset($slide['status']) and ($slide['status'] == "fail")) {
				++$failed;
			}
		}
		return $failed;
	}
	
	public function calculate_rating () {
		
		$plethos = 0;
		$sum = 0;
		$slides = $this->embryo_slides;
		
		$slides_rated = array();
		
		array_shift($slides); 
		
#		echo "<pre>";
#		var_dump($slides);
#		echo "</pre>";
		
		foreach ($slides as $slide) {
			if ( ($slide['status'] != "fail") and isset($slide['section_rating']) and ($slide['section_rating'] != 0)) {
				$slides_rated[] = $slide['id'];
				++$plethos;
#				echo $slide['section_rating']."T<br />";
				$sum += $slide['section_rating'];
			}
		}
		
#		echo "<pre>";
#		var_dump($slides_rated);
#		echo "</pre>";
		
		if ($plethos == 0)
		{
			$this->animal_rating = 0;
			$this->slides_rated = array(0);
		}
		else
		{
			$this->animal_rating = sprintf("%.2f",($sum/$plethos));
			$this->slides_rated = $slides_rated;
			
		}
	}
	

/*	
	private function how_many_blank_slides () {
		
		$blank = 0;
		foreach ($this->embryo_slides as $slide) {
			echo "<pre>";
			var_dump($slide);
			echo "</pre>";
			if (isset($slide['experiment_date']) and (strlen($slide['experiment_date']) == 0)) {
				++$blank;
				
				
				
			}
		}
		return $blank;
	}
*/	
	private function how_many_sections() {
		
		$sections = 0;
		
		foreach ($this->embryo_slides as $slide) {
			if (isset($slide['section_count'])) {
				$sections += $slide['section_count'];
			}
		}
		return $sections;
	}
	
	public function printSlideTable ($basket = "basketOFF", $addedSlides=array("test")) {
				
		$display_block = "<table class=\"slidetable\">\n";
		$display_block .= "<tr>";
		$display_block .= "<th class=\"narrowth\"></th>";
		for ($i=1;$i<=$this->embryo_data['set_width'];++$i) {
			$display_block .= "<th>".$this->header_row_el[$i]."</th>";
		}
		$display_block .= "</tr>\n";
		
		// this loop builds each row - $w is the row id
		for ($w=0;$w<$this->embryo_data['set_height'];++$w) {
			
			$display_block .= "<tr>";
			$display_block .= "<th class=\"narrowth\">";
			$display_block .= "<a href=\"#sectionSlides\" onclick=\"loadLine('".$this->embryo_id."', '".($w+1)."')\" >".($w+1)."</a>";
			$display_block .= "</th>";
#			$display_block .= "<th class=\"narrowth\">".($w+1)."</th>";
			
			// this loop builds columns - $f is the column if
			for ($f=1;$f<=$this->embryo_data['set_width'];++$f) {
				// this var creates the cell id
				$indie_cell_id = $f+($this->embryo_data['set_width']*$w);
				
				// if there is content inside experiment_date
				if ($this->embryo_slides[$indie_cell_id]['experiment_date'] != 0 ) {
					
					
					$if_exist_sections = ($this->embryo_slides[$indie_cell_id]['section_count'] > 0) ? "style=\"color:#98FB98;\"" : "style=\"color:red;\"";
					// if our experiment has NOT failed
					if ($this->embryo_slides[$indie_cell_id]['status'] != "fail") { 
						$gene_color = explode(".", $this->embryo_slides[$indie_cell_id]['colour']);
						$display_block .= "<td>".$this->format_date($this->embryo_slides[$indie_cell_id]['cut_date'])."<br>";
						
					//	when restricted users view, sections have to be viewed underneath via ajax!
						if ($this->restriction_flag == "OFF") {

							$display_block .= "<a href=\"sections.php?opr=select&slide_id=".$this->embryo_slides[$indie_cell_id]['id']."\">";
							$display_block .="<i class=\"fa fa-circle fa-fw\" aria-hidden=\"true\" ".$if_exist_sections."></i>";
							$display_block .="</a>";

							$display_block .="&nbsp;";


						}/* elseif ($this->restriction_flag == "PROTECT") {
							$display_block .= "&nbsp;";
						} */else {
							
							$display_block .= "<a href=\"#sectionSlides\" onclick=\"loadDoc('".$this->embryo_slides[$indie_cell_id]['id']."')\"><i class=\"fa fa-circle fa-fw\" aria-hidden=\"true\" ".$if_exist_sections."></i></a>&nbsp;";
							
							
						}
						
						$display_block .= "<span style=\"background-color:#".$gene_color[0].";color:#".$gene_color[1].";\">(".$this->embryo_slides[$indie_cell_id]['solBook'].") ".$this->embryo_slides[$indie_cell_id]['gene']."</span>&nbsp;";
												
					// if our experiment HAS failed
					} else {
						$display_block .= "<td class=\"fail\">".$this->format_date($this->embryo_slides[$indie_cell_id]['cut_date'])."<br>(".$this->embryo_slides[$indie_cell_id]['solBook'].") ".$this->embryo_slides[$indie_cell_id]['gene'];
					}
					
					// the >> hyperlink must be visible only to users with permission!
					if ($this->restriction_flag == "OFF") {
						
							$display_block .= "<a href=\"recordare.php?action=add_slide&slide_id=".$this->embryo_slides[$indie_cell_id]['id']."\"><i class=\"fa fa-angle-double-right fa-fw\" aria-hidden=\"true\"></i></a>";

					}
					
					$display_block .= "<br><a href=\"".FRONTPAGE."?opr=exper&showDate=".$this->embryo_slides[$indie_cell_id]['experiment_date']."\">".$this->format_date($this->embryo_slides[$indie_cell_id]['experiment_date'])."</a></td>";
					
				} else {
					if ($this->restriction_flag == "OFF") {
						if ($basket == "basketON") {
							if ($addedSlides[0] == "test") {
								$display_block .= "<td><div id=\"section_".$this->embryo_slides[$indie_cell_id]['id']."\"><a href=\"javascript:void(0);\" onclick=\"addSildeToBasket(".$this->embryo_slides[$indie_cell_id]['id'].");\">(+)</a></div></td>";
							} else {
								$found = "NO";
								for ($o=0;$o<count($addedSlides);++$o) {
									if ($addedSlides[$o] == $this->embryo_slides[$indie_cell_id]['id']) {
										// FOUND
										$found = "YES";
										break;
									} else {
										continue;
									}
								}
								if ($found == "YES") {
									$display_block .= "<td>added</td>";
									$found = "NO";
								} else {
									$display_block .= "<td><div id=\"section_".$this->embryo_slides[$indie_cell_id]['id']."\"><a href=\"javascript:void(0);\" onclick=\"addSildeToBasket(".$this->embryo_slides[$indie_cell_id]['id'].");\">(+)</a></div></td>";
								}
							}
							
						} else {
							$display_block .= "<td><a href=\"recordare.php?action=add_slide&slide_id=".$this->embryo_slides[$indie_cell_id]['id']."\">(+)</a></td>";
						}
					} else {
						$display_block .= "<td>(+)</td>";
					}
				}
			}
			$display_block .= "</tr>\n";
		}
		
		if ($this->restriction_flag == "OFF") {
			$display_block .= "<tr><td colspan=".($this->embryo_data['set_width']+1)."><a href=\"recordare.php?action=add_row&embryo_id=".$this->embryo_id."&width=".$this->embryo_data['set_width']."\">add row</a></td></tr>\n";
		}
		
		$display_block .= "</table>\n";

		return $display_block;
	}
	
	public function printEmitterTable($icons_dir_rel_path = "icons/") {
		$display_block = "<table id=\"emitterTable\">\n";
		$display_block .= "<tr>";
		
		for ($i=0;$i<=$this->embryo_data['set_width'];++$i) {
			$display_block .= "<th>".$this->header_row_el[$i]."</th>";
		}
		$display_block .= "</tr>\n";
		
		// this loop builds each row - $w is the row id
		for ($w=0;$w<$this->embryo_data['set_height'];++$w) {
			
			$display_block .= "<tr>";
			$display_block .= "<th>".($w+1)."</th>";
			
			// this loop builds columns - $f is the column if
			for ($f=1;$f<=$this->embryo_data['set_width'];++$f) {
				// this var creates the cell id
				$indie_cell_id = $f+($this->embryo_data['set_width']*$w);
				
				if ($this->embryo_slides[$indie_cell_id]['experiment_date'] != 0 ) {
				
					if (($this->embryo_slides[$indie_cell_id]['status'] != "fail") && ($this->embryo_slides[$indie_cell_id]['section_count'] != false) ) {
						$display_block .= "<td style=\"background-image: url('".$icons_dir_rel_path.$this->header_row_en[$f].($w+1).".png');\">";
						$display_block .= "<div class=\"div2\" id=\"drag".$this->embryo_slides[$indie_cell_id]['id']."\" ondrop=\"drop(event)\" ondragover=\"allowDrop(event)\">";
						$display_block .= "<img src=\"".$icons_dir_rel_path.$this->embryo_slides[$indie_cell_id]['gene'].".png\" draggable=\"true\" ondragstart=\"drag(event)\" id=\"".$this->embryo_slides[$indie_cell_id]['id']."\" alt=\"".$this->embryo_slides[$indie_cell_id]['gene']."\" style=\"width: 66px; height: 45px;\">";
						$display_block .= "</div>";
						$display_block .= "</td>\n";
					} else {
						$display_block .= "<td style=\"background-image: url('".$icons_dir_rel_path."failed.png');\">";
						$display_block .= $this->embryo_slides[$indie_cell_id]['gene'];
						$display_block .= "</td>\n";
					}
				} else {
					$display_block .= "<td>pending</td>\n";
				}
			}
			$display_block .= "</tr>\n";
		}
		
		$display_block .= "</table>\n";
		
		return $display_block;
	}
	
	public function printReceiverTable() {
		$display_block = "<table id=\"receiverTable\">\n";
		$display_block .= "<tr class=\"header_row\">";
		
		for ($i=0;$i<=$this->embryo_data['set_width'];++$i) {
			$display_block .= "<th>".$this->header_row_el[$i]."</th>";
		}
		$display_block .= "</tr>\n";
		
		// this loop builds each row - $w is the row id
		for ($w=0;$w<$this->embryo_data['set_height'];++$w) {
			
			$display_block .= "<tr class=\"row_no_".$w."\">";
			$display_block .= "<th>".($w+1)."</th>";
			
			// this loop builds columns - $f is the column if
			for ($f=1;$f<=$this->embryo_data['set_width'];++$f) {
				// this var creates the cell id
				$indie_cell_id = $f+($this->embryo_data['set_width']*$w);
				$display_block .= "<td>";
				$display_block .= "<div class=\"div3\" id=\"".$indie_cell_id."\" ondrop=\"drop(event)\" ondragover=\"allowDrop(event)\"></div>";
				$display_block .= "</td>\n";
			}
			$display_block .= "</tr>\n";
		}
		
		$display_block .= "</table>\n";
		
		return $display_block;
	}
	
	public function addOperation ($name, $when_date, $comments) {
		
		
		$sql = "insert into operations (name, when_date, embryo_id, comments) values (?, ?, ?, ?)";
		
		try {
			$this->db->beginTransaction();
			$prepare = $this->db->prepare($sql);
			$prepare->bindValue(1, $name, PDO::PARAM_STR);
			$prepare->bindValue(2, $when_date, PDO::PARAM_LOB);
			$prepare->bindValue(3, $this->embryo_id, PDO::PARAM_INT);
			$prepare->bindValue(4, $comments, PDO::PARAM_STR);
			$prepare->execute();
			
			$this->db->commit();
		}
		catch (Exception $e) {
			$this->db->rollback;
			throw $e;
		}
	}
	
	public function addSlideRow ($when_date, $newSet_height, $comments, $newSet_width=0) {
		
		$switch = "updateSet";
		
		if ($this->restriction_flag != "OFF") {
			return "permission denied";
		}
		
		if ($newSet_height < 1) {
			return "invalid height";
		}
		
		if ($newSet_width == 0) {
			$newSet_width = $this->embryo_data['set_width'];
		} else {
			$switch = "novelSet";
		}
		
		if ($switch == "updateSet") {
			$new_height = $this->embryo_data['set_height'] + $newSet_height;
			
			$sql = "update embryo set set_height=? where id=?";
			$prepare = $this->db->prepare($sql);
			$prepare->bindValue(1, $new_height, PDO::PARAM_INT);
			$prepare->bindValue(2, $this->embryo_id, PDO::PARAM_INT);
			$prepare->execute();
			
			$this->addOperation("sectioning", $when_date, $comments);
			
		}
		
		// update table 'slide'
		
		try {
			$this->db->beginTransaction();
			$sql_slide = "insert into slide (embryo_id, name, slide_position, cut_date) values (?, ?, ?, ?)";
			$slide_prepare = $this->db->prepare($sql_slide);			
			
			for ($h=$this->embryo_data['set_height']; $h<$new_height; ++$h) {
				for ($w=1; $w<=$this->embryo_data['set_width']; ++$w) {
					$slide_id = $w + $this->embryo_data['set_width']*$h;
					$lettering = $this->header_row_el[$w].($h+1);
						
					$parameters = array($this->embryo_id, $lettering, $slide_id, $when_date);
					$slide_prepare->execute($parameters);
						
				}
			}
			
			$this->db->commit();
			
			
			
		} catch (Exception $e) {
			$this->db->rollback;
			throw $e;
			
		}
		
	}
	
	public function __destruct() { }

}

class madre {
	
	public $db;
	public $restriction_flag;
	public $madre_id;
	public $madre_info = array();
	
	public $embryo_age;
	
	public $embryo_names = array();
	public $embryo_ids = array();
	
	public $crl_average;
	
	public $allPictures;
	
	public function __construct ($db, $madre_id, $restriction_flag="ON") {
		$this->db = $db;
		$this->restriction_flag = $restriction_flag;
		$this->madre_id = $madre_id;

#		$madre_sql = "select madre.*, embryo.age as embryo_age, count(embryo.id) as embryos_registered, group_concat(embryo.name) as embryos, avg(embryo.crl) as crl_average, group_photos.file_name from embryo left join madre on embryo.madre_id = madre.id left join group_photos on group_photos.madre_id = madre.id where embryo.part != 'body' and madre.id=?";
		
		$madre_sql = "select * from madre where id=?";

		$madre_ret = $db->prepare($madre_sql);
		$madre_ret->bindValue(1, $this->madre_id, PDO::PARAM_INT);
		$madre_ret->execute();
		$madre = $madre_ret->fetch(PDO::FETCH_ASSOC);
		
		$this->madre_info = $madre;

#		$this->embryo_names = explode(",", $madre['embryos']);
#		$this->group_picture = $madre['file_name'];
		
		$this->fetch_related_embryos();
#		$this->fetchAllRelatedPics();
		
#		echo "<pre>";
#		print_r($madre);
#		echo "</pre>";
		

	}
	
	#		$madre_sql = "select madre.*, embryo.age as embryo_age, count(embryo.id) as embryos_registered, group_concat(embryo.name) as embryos, avg(embryo.crl) as crl_average, group_photos.file_name from embryo left join madre on embryo.madre_id = madre.id left join group_photos on group_photos.madre_id = madre.id where embryo.part != 'body' and madre.id=?";
	
	public function fetch_related_embryos () {
		
		$crls = array();
		
		$sql = "select id, name, part, cut, age, set_width, set_height, crl from embryo where madre_id=? and part != 'body'";
		$emb_ret = $this->db->prepare($sql);
		$emb_ret->bindValue(1, $this->madre_id, PDO::PARAM_INT);
		$emb_ret->execute();
		$embryos = $emb_ret->fetchAll(PDO::FETCH_ASSOC);
		
		$this->embryo_age = $embryos[0]['age'];
		
		foreach ($embryos as $one) {
			$this->embryo_names[] = $one['name'];
			$this->embryo_ids[] = $one['id'];
			$crls[] = $one['crl'];
		}
		
		$crls_clean = array_filter($crls);
		
		$this->crl_average = count($crls_clean) ? sprintf("%01.2f mm", (array_sum($crls_clean)/count($crls_clean))) : 0;
		
#		if (count($crls_clean)) {
#			$this->crl_average = array_sum($crls_clean)/count($crls_clean);
#		}
		

		
	}
	
	public function fetchRelatedPics ($photos = "madre") {
		
		$sql = "select id, file_name, group_descr from group_photos where madre_id = ?";
		$group_ret = $this->db->prepare($sql);
		$group_ret->bindValue(1, $this->madre_id, PDO::PARAM_INT);
		$group_ret->execute();
		$groupPhotos = $group_ret->fetchAll(PDO::FETCH_ASSOC);
		
#		$this->group_picture = $groupPhotos[0];
		
#		$groupPhotos = $group_ret->fetchAll(PDO::FETCH_ASSOC);
		
		
		
		$allPics = array();
		
		foreach ($groupPhotos as $photo) {
			$allPics[] = ["group", $photo['file_name'], $this->madre_id, $photo['id'], $photo['group_descr']];
		}
		
		
		if ($photos == "all") {
			$marks = implode(",", array_fill(0, count($this->embryo_ids), "?"));
			$sql2 = "select embryo_photos.id as embryo_photo_id, embryo_photos.file_name, embryo_photos.embryo_pic_descr, embryo.name from embryo_photos inner join embryo on embryo_photos.embryo_id=embryo.id where embryo_photos.embryo_id in (".$marks.")";
		
			$emb_ret = $this->db->prepare($sql2);
	#		$group_ret->bindValue(1, $this->madre_id, PDO::PARAM_INT);
			$emb_ret->execute($this->embryo_ids);
			$embryoPhotos = $emb_ret->fetchAll(PDO::FETCH_ASSOC);
			foreach ($embryoPhotos as $photo) {
				$allPics[] = ["embryo", $photo['file_name'], $photo['name'], $photo['embryo_photo_id'], $photo['embryo_pic_descr']];
			}
		}
		
		if (count($allPics) < 1) {
			$this->allPictures = "not_found";
		} else {
			$this->allPictures = $allPics;
		}
		
#		echo "<pre>";
#		print_r($this->embryo_ids);
#		print_r($this->db->ErrorInfo());
#		print_r($allPics);
#		print_r($embryoPhotos);
#		echo "</pre>";
		
	}
	
}

class sections {
	
	public $db;
	public $restriction_flag;
	public $slide_id;
	public $max_sections=0; // how many sections should be there (by means of position on slide)
	public $sections;
	public $embryo_info = array();
	
		
	public function __construct($db, $slide_id, $restriction_flag="ON") {
		$this->db = $db;
		$this->restriction_flag = $restriction_flag;
		$this->slide_id = $slide_id;
		$this->embryo_info = $this->getEmbryoInfo();
	
		$sql_get_all_sections = "select * from section where slide_id=? order by pos_on_slide asc";
		$section_prep = $db->prepare($sql_get_all_sections);
		$section_prep->bindValue(1, $slide_id, PDO::PARAM_INT);
		$section_prep->execute();
		$fetched_sections = $section_prep->fetchAll(PDO::FETCH_ASSOC);
		
		if (!is_array($fetched_sections) || ($fetched_sections == FALSE) || empty($fetched_sections)) {
			return $this->sections=0;
		} else {
			array_walk($fetched_sections, function (&$section, $i) {
				settype($section["id"], "integer");
				settype($section["slide_id"], "integer");
				settype($section["rating"], "float");
			});
			
			foreach ($fetched_sections as $section) {
				if ($section['pos_on_slide'] > $this->max_sections) {
					$this->max_sections = $section['pos_on_slide'];
				} else {
					continue;
				}
			}
			
			$transformed_sections = array();
		
			$j = 0;
			for ($i=0;$i<$this->max_sections;++$i) {
				if ($fetched_sections[$j]['pos_on_slide'] == ($i+1)) {
					$transformed_sections[] = $fetched_sections[$j];
					++$j;
				} else {
					$transformed_sections[] = "blank";
				}
			
			}
			
			
		
			$this->sections = $transformed_sections;
			
			return 1;
		
		
		
		
#		echo "<pre>CLASS";
#		var_dump($fetched_sections);
#		echo "</pre>";
		
	
		
			
#			$transformed_sections = array("ZERP");
		}
#echo "<pre>";
#print_r($fetched_sections);
#print_r($transformed_sections);
#echo $this->max_sections;
#echo "</pre>";		
		
	}
	
	public function section_table ($columns) {
		
		$rows = ceil($this->max_sections / $columns);
		
		// round up the table
		$difference = ($rows*$columns) - $this->max_sections;
		while ($difference-->0) {
			$this->sections[] = "blank";
		}
		
	
		$pic_block = "<table class=\"slideSections\">\n";
		for ($row=0;$row<$rows;++$row) {
			$pic_block .= "<tr>\n";
			for ($col=0;$col<$columns;++$col) {
				$pic_id = $col + $row*$columns;
				$pic_block .= "<td>";
				$pic_block .= "<div class=\"insideImageText\">";
				if ($this->sections[$pic_id] != "blank") {
					$pic_block .= "<a href=\"".$this->sections[$pic_id]['file_name']."\" target=\"_blank\">";
					$pic_block .= "<img class=\"slideSections\" src=\"".$this->sections[$pic_id]['file_name']."\" alt=\"".$this->sections[$pic_id]['section_name']."\" >";
					$pic_block .= "</a>";
					if ($this->restriction_flag == "OFF") {
						$pic_block .= "<div class=\"top-left\">(+)</div>";
					}
				} else {
					$pic_block .= ($pic_id+1)."<br>";
					$pic_block .= "section missing";
				}
				
				$pic_block .= "</div>";
				$pic_block .= "</td>\n";
			}
			$pic_block .= "</tr>\n";	
		}
	
		$pic_block .= "</table>";
		
		return $pic_block;
	}
	
	public function section_tableDIV ($columns) {
		
		$rows = ceil($this->max_sections / $columns);
		
		// round up the table
		$difference = ($rows*$columns) - $this->max_sections;
		while ($difference-->0) {
			$this->sections[] = "blank";
		}
		
		$pic_block = "";
	
		
		foreach ($this->sections as $section)
		{
			
#			echo "<pre>";
#			var_dump($section);
#			echo "</pre>";
			
			
			$pic_block .= "<div class=\"responsive\">\n";
			
			$pic_block .= "<div class=\"gallery\">";
			
			if (is_array($section))
			{
				
				$pic_block .= "<a href=\"".$section['file_name']."\" target=\"_blank\">";
				$pic_block .= "<img class=\"slideSections\" src=\"".$section['file_name']."\" alt=\"".$section['section_name']."\" >";
				$pic_block .= "</a>";
				
			}
			else
			{
#				$pic_block .= "<div class=\"column\">";
				$pic_block .= "section missing";
#				$pic_block .= "</div>";
				
			}
			$pic_block .= "</div>";
			$pic_block .= "</div>";
			
		}
		
	
		
		
		return $pic_block;
	}
	
	
	protected function getEmbryoInfo () {
		$get_slide_info_sql = "
	select 	slide.name as slide_name, 
			slide_position, 
			experiment_date, 
			cut_date, 
			gene.id as gene_id,
			gene.gene_name,
			gene.solution_book,
			embryo.name as embryo_name,
			embryo.part,
			embryo.cut,
			embryo.age
	from slide 
	inner join gene on gene.id=slide.gene 
	inner join embryo on slide.embryo_id=embryo.id 
	where slide.id=?
		";
		$prepare = $this->db->prepare($get_slide_info_sql);
		$prepare->bindValue(1, $this->slide_id, PDO::PARAM_INT);
		$prepare->execute();
		$embryo_info = $prepare->fetch(PDO::FETCH_ASSOC);
		return $embryo_info;
	}
	
	public function show_upblock ($pointer = "out") {

		$up_block = "";
			
		$up_block .= "<a href=\"".FRONTPAGE."?opr=showem&name=".$this->embryo_info['embryo_name']."\">F".$this->embryo_info['embryo_name']." ".$this->embryo_info['part']."</a> (<a href=\"".FRONTPAGE."?opr=showag&age=".$this->embryo_info['age']."\">E".$this->embryo_info['age']."</a>) | ";
		$up_block .= "slide: ".$this->embryo_info['slide_name'];
		$up_block .= " (<a href=\"manage_probes.php?opr=".$this->embryo_info['gene_id']."\" target=\"_blank\">".$this->embryo_info['solution_book']." ".$this->embryo_info['gene_name']."</a>) | ";
		$up_block .= "cut: ".format_date($this->embryo_info['cut_date'])." (".$this->embryo_info['cut'].") | ";
		$up_block .= "experiment: <a href=\"".FRONTPAGE."?opr=exper&showDate=".$this->embryo_info['experiment_date']."\">".format_date($this->embryo_info['experiment_date'])."</a>";
		
		if ($pointer == "select")
		{$up_block .= "&nbsp;|&nbsp;(<a href=\"sections.php?opr=multiple_add&slide_id=".$_GET['slide_id']."\">see details</a>)";}
		elseif ($pointer == "multiple_add")
		{$up_block .= "&nbsp;|&nbsp;(<a href=\"sections.php?opr=select&slide_id=".$_GET['slide_id']."\">block view</a>)";}
		else {$up_block .= ".";}
		return $up_block;
	}
	
	public function showImg ($position_id, $max_sec_on_slide = 20) {
		
		$form = "";
		$slide_info_null = array( 'id' => 0, 'pos_on_slide' => 0, 'section_name' => "", 'file_name' => "", 'rating' => 1, 'comments' => "" );
		$slide_info = array();
		
		
		if ( ($position_id < $this->max_sections) && (is_array($this->sections[$position_id])) ) {
			$slide_info = $this->sections[$position_id];
		} elseif ($position_id >= $this->max_sections) {
			$slide_info = $slide_info_null;
			$slide_info['pos_on_slide'] = $position_id+1;
		} else {
			$slide_info = $slide_info_null;
		}
		
		$form .= "<div class=\"section_info_container\">";
		$form .= "<div class=\"section_info_left\">";
		$form .= "<form action=\"sections.php?opr=add_section\" method=\"POST\">";
		$form .= "<input type=\"hidden\" name=\"slide_id\" value=\"".$this->slide_id."\">";
		$form .= "<input type=\"hidden\" name=\"section_id\" value=\"".$slide_info['id']."\">";
		$form .= "<div class=\"section_name\">Section name: <input type=\"text\" size=\"50\" name=\"section_name\" value=\"".$slide_info['section_name']."\"></div>";
		$form .= "<div class=\"section_position\">Position on slide: <select name=\"pos_on_slide\">\n";
		
		for ($j=1;$j<=$max_sec_on_slide;++$j) {
			$form .= "<option value=\"".$j."\"";
			
			if ($j == $slide_info['pos_on_slide']) {
				$form .= " selected=\"selected\"";
			}
			
			$form .= ">".$j."</option>\n";
		}
		
		$form .= "</select>\n</div>\n";
		
		$form .= "<div class=\"section_rating\">Rating: <select name=\"rating\">";
		for ($j=1; $j<=5; ++$j) {
	#		$form .= "<option value=\"".$j."\">".$j."</option>";
			$form .= "<option value=\"".$j."\"";
			if ($j == $slide_info['rating']) {
				$form .= " selected";
			}
			
			$form .= ">".$j."</option>\n";
		}
		$form .= "</select></div>\n";
		
		$form .= "<div class=\"section_comments\">Comments: <br />";
		$form .= "<textarea name=\"comments\">".$slide_info['comments']."</textarea></div>";
		$form .= "<div class=\"section_name\">Filename: <input type=\"text\" size=\"50\" name=\"file_name\" value=\"".$slide_info['file_name']."\"></div>";


		$form .= "<input type=\"submit\" class=\"button\" value=\"Change\"></form></div>";
		$form .= "<div class=\"section_info_right\">";
		if (isset ($this->sections[$position_id]['section_name'])) {
			$form .= "<img style=\"width: 200px;\" alt=\"".$this->sections[$position_id]['section_name']."\" src=\"".$this->sections[$position_id]['file_name']."\"></img>";
		} else {
			$form .= "<img style=\"width: 200px;\" alt=\"enter image filename\" src=\"".REL_ICONS_LOCUS."image_pending.png\"></img>";
		}

		$form .= "</div>";
		$form .= "</div>";

	
	return $form;	
		
	}
	
	public function rawInputForm ($max_sec_on_slide = 20) {
		
		$section_break = 5; # max sections per row on slide
		$row_pointer = 1;
		$section_on_row_pointer = 1;
		
		$general_section_name = "F".$this->embryo_info['embryo_name']."_".$this->embryo_info['slide_name']."_";
		

		$form_const_element = "";
		for ($i=1;$i<=$max_sec_on_slide;++$i) {
			
			$local_section_name = $general_section_name."S".$row_pointer."T".$section_on_row_pointer;
			if ($section_on_row_pointer == $section_break) {
				$section_on_row_pointer = 1;
				++$row_pointer;
			} else {
				++$section_on_row_pointer;
			}
			
			if ($i == $max_sec_on_slide) {
				$row_pointer = 1;
			}
			
			$form_const_element .= "\n";
			$form_const_element .= "<div class=\"form_row\">\n";
			
			$form_const_element .= "<div class=\"form_row_section_pos\">".$i."</div>\n";

			$form_const_element .= "<div class=\"form_row_section_name\">";
			$form_const_element .= "<input type=\"text\" size=\"30\" name=\"section_".$i."[name]\" value=\"".$local_section_name."\"> ";
			$form_const_element .= "</div>\n";

			$form_const_element .= "<div class=\"form_row_section_path\">";
			$form_const_element .= "<input type=\"text\" size=\"50\" name=\"section_".$i."[filename]\" placeholder=\"path - filename\"><br />\n";
			$form_const_element .= "</div>\n";
			
			$form_const_element .= "</div>\n";
		}
		
		
		return $form_const_element;

		
	}
}

class allEmbryos {
	
	public $db;
	
	public $embryos_array;
	
	public function __construct ($db) {
		$this->db = $db;
				
		$sql = "select * from embryo where part != 'body'";
		$prep = $db->prepare($sql);
		$prep->execute();
		$embryos = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		$this->embryos_array = $embryos;
		
	}
	
	public function makeEmbryoSimpleList () {
		
		$list = array();
		
		foreach ($this->embryos_array as $embryo) {
			$list[] = array('id' => $embryo['id'], 'name' => $embryo['name'], 'age' => $embryo['age']);
		}
		
		return $list;
	}
	
	public function selectListItems () {
		
		$out_block = "";
		
		$list = $this->makeEmbryoSimpleList();
		
#		echo "<pre>";
#		print_r($list);
#		echo "</pre>";
		
		foreach ($list as $embryo) {
			$out_block .= "<option value=\"".$embryo['id']."\">".$embryo['name']." - E".$embryo['age']."</option>\n";
		}
		
		return $out_block;
	}
	
}

class eraser extends embryo {
	
	public $slideIdsArray;
	
	public function get_slide_ids ()
	{
		$slide_ids = array();
		
		if ($this->embryo_slides == false) {

			$this->relatedSlides();
		}
		
		foreach ($this->embryo_slides as $slide) {
			
			if (is_array($slide)) {
				$slide_ids[] = $slide['id'];
			} 
			else 
			{
				continue;
			}
			
		}
		$this->slideIdsArray = $slide_ids;
	}
	
	public function deleteSlides()
	{
		
		if ($this->restriction_flag !== "OFF") {
			return 0;
		}

		
		try {
			$this->db->beginTransaction();
	#		$marks = implode(",", array_fill(0, count($this->slideIdsArray), "?"));
		
			$del_sql = "delete from slide where embryo_id=?";
			$prepare = $this->db->prepare($del_sql);
			$prepare->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
			$prepare->execute();
			
			$set_sql = "update embryo set cut='', set_width='', set_height='' where id=?";
			$prepare_set = $this->db->prepare($set_sql);
			$prepare_set->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
			$prepare_set->execute();
			
			$this->db->commit();
			
#			$del_slides_ret = $this->db->prepare($del_sql);

#			$del_slides_ret->execute($this->slideIdsArray);
			
		} catch (Exception $e) {
			$this->db->rollback;
			throw $e;
			
		}
			
	}
	
	public function deleteSections()
	{
		
		if ($this->restriction_flag !== "OFF") {
			return 0;
		}

		
		if (count($this->slideIdsArray) == 0)
		{
			$this->get_slide_ids();
		}
		
#		echo "<pre>DUMPER";
#		var_dump($this->slideIdsArray);
#		echo "</pre>";
		
		if ($this->sections_count == 0) {
			return 0;
		}
		
		try {
			$this->db->beginTransaction();
			$marks = implode(",", array_fill(0, count($this->slideIdsArray), "?"));
		
			
			$del_sql = "delete from section where slide_id in (".$marks.")";
			$prepare = $this->db->prepare($del_sql);

			$prepare->execute($this->slideIdsArray);
			

			
			
			$this->db->commit();
			
			return 1;
			
			
		} catch (Exception $e) {
			$this->db->rollback;
			throw $e;
			return 0;
			
		}
		
		
	}
	
	private function deleteOperations()
	{
		
		if ($this->restriction_flag !== "OFF") {
			return 0;
		}
		
		try {
			$this->db->beginTransaction();
	
#			$del_sql_1 = "delete from operations where embryo_id in (select id from embryo where name=?)";
			$del_sql = "delete from operations where embryo_id=?";
			$prepare = $this->db->prepare($del_sql);
			$prepare->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
			$prepare->execute();
									
			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollback;
			throw $e;
			
		}
	}
	
	private function deleteEmbryoPictures()
	{
		
		if ($this->restriction_flag !== "OFF") {
			return 0;
		}
		
		try {
			$this->db->beginTransaction();
	
			$del_sql = "delete from embryo_photos where embryo_id=?";
			$prepare = $this->db->prepare($del_sql);
			$prepare->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
			$prepare->execute();
									
			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollback;
			throw $e;
			
		}
	}
	
	public function deleteEmbryo() {
		
		if ($this->restriction_flag !== "OFF") {
			echo "invalid permission<br />".$this->restriction_flag;
			return 0;
		}

		$this->deleteOperations();
		if (is_array($this->embryo_pictures)) {
			$this->deleteEmbryoPictures();
		}
		
		
		try {
			$this->db->beginTransaction();
	
			$del_sql = "delete from embryo where id=?";
			$prepare = $this->db->prepare($del_sql);
			$prepare->bindValue(1, $this->embryo_id, PDO::PARAM_INT);
			$prepare->execute();
									
			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollback;
			throw $e;
			
		}
		
	}
	
	public function __destruct() { }
	
	
}

?>