<?php

class experiment {
	
	public $db;
	public $restriction_flag;
	public $slide_ids;
	public $experiment_date;
#	public $date_obj;
	
	
	public function __construct ($db, $experiment_ident, $restriction_flag="ON") {
		$this->db = $db;
		$this->restriction_flag = $restriction_flag;
		
	
		if (is_array($experiment_ident)) {
			foreach ($experiment_ident as $slide_id) {
				if (!is_numeric($slide_id)) {
					echo "experiment init failed. no valid array input";
					die;
				}
			}
			$this->slide_ids = $experiment_ident;
		} elseif (is_numeric($experiment_ident)) {
			settype($experiment_ident, "integer");
			if ($experiment_ident < 201510281200) {
				echo "Invalid experiment date: ".$experiment_ident;
				die;
			}
			$this->experiment_date = $experiment_ident;
#			$this->setTimeObject();
			$this->slide_ids = $this->getSlideIdsByDate();
		} else {
			echo "experiment init failed";
			die;
		}
			
	}

	private function format_date ($input_date) {
	#	$months_en = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		return substr($input_date, 0, 4)."-".substr($input_date, 4, 2)."-".substr($input_date, 6, 2);
	}
	
	private function setTimeObject () {
			$this->date_obj = new DateTime($this->format_date($this->experiment_date));
#			echo "<pre>";
#			var_dump($this->date_obj);
#			echo "</pre>";
			
	}
	

	
	protected function getSlideIdsByDate () {
		$sql = "select id from slide where experiment_date like ?";
		$prep = $this->db->prepare($sql);
		$prep->bindValue(1, $this->experiment_date, PDO::PARAM_INT);
		$prep->execute();
		$ids = $prep->fetchAll(PDO::FETCH_COLUMN, 0);

#		echo "<pre>";
#		print_r($prep->errorInfo());
#		echo "</pre>";

		if (sizeof($ids) < 1) {
			return 0;
		} else {
			return $ids;
		}
		
#		echo count($ids);
#echo	count($this->slide_ids);
	}
	
	protected function getSlideInfo () {
		// requires experiment_date to be loaded
#echo	count($this->slide_ids);
		$in = str_repeat("?, ", count($this->slide_ids)-1) . "?";
		$sql0 = "select embryo.name as embryo_name, embryo.id as embryo_id, embryo.age, slide.name as slide_name, slide.slide_position as slide_id, slide.id as slide_ori_id, slide.status as slide_status, gene.gene_name, gene.colour from slide inner join embryo on slide.embryo_id=embryo.id inner join gene on slide.gene=gene.id where slide.id in (".$in.") order by embryo.age asc";
		
		$sql1 = "select embryo.name as embryo_name, embryo.id as embryo_id, embryo.age, slide.cut_date, slide.name as slide_name, slide.slide_position as slide_id, slide.id as slide_ori_id, slide.status as slide_status, gene.gene_name, gene.solution_book, gene.colour from slide inner join embryo on slide.embryo_id=embryo.id inner join gene on slide.gene=gene.id where slide.id in (".$in.") order by embryo.age asc";
		
		$sql = "select 
			embryo.name as embryo_name, 
			embryo.id as embryo_id, 
			embryo.age, 
			slide.cut_date, 
			slide.name as slide_name, 
			slide.slide_position as slide_id, 
			slide.id as slide_ori_id, 
			slide.status as slide_status,
			(select avg(section.rating) from section where section.slide_id = slide.id) as slide_score,
			(select count(id) from section where section.slide_id = slide.id) as section_count, 
			gene.gene_name, 
			gene.solution_book, 
			gene.colour 
		from slide 
			inner join embryo on slide.embryo_id=embryo.id 
			inner join gene on slide.gene=gene.id
			where slide.id in (".$in.")
		order by embryo.age asc";
		
		$prep = $this->db->prepare($sql);

		$prep->execute($this->slide_ids);


#		return $res = $prep->fetchAll(PDO::FETCH_ASSOC);
		
		$res = $prep->fetchAll(PDO::FETCH_ASSOC);
		
#		echo "<pre>";
#		print_r($res);
#		print_r($prep->errorInfo());
#		echo "</pre>";
		
		return $res;
	}
	
	public function printTable () {
				
		$out_block = "<table class=\"massive_table\">\n";
		$out_block .= "<tr><th>age</th><th>embryo</th><th>slide</th><th>probe</th><th>cut date</th><th>status<br />(score)</th></tr>\n";
		
		$res = $this->getSlideInfo();
	

		
		$experiment_date_stamp = strtotime($this->format_date($this->experiment_date));

#		echo "<pre>";
#		var_dump($experiment_date_stamp);
#		echo "</pre>";
	
		foreach ($res as $slide) {
			$colors = explode(".", $slide['colour']);
			$out_block .= "<tr>";
			$out_block .= "<td>".$slide['age']."</td>";
			$out_block .= "<td><a href=\"frontpage.php?opr=showem&name=".$slide['embryo_name']."\">".$slide['embryo_name']."</a></td>";
			$out_block .= "<td>";
			
			if ($slide['section_count'] > 0) {
				
				$style = "style=\"color:#98FB98;\"";
				
			} else {
				$style = "style=\"color:red;\"";
			}
			
#			$if_exist_sections = ($slide['section_count'] > 0) ? "style=\"color:#98FB98;\"" : "style=\"color:red;\"";
			
			
			$out_block .= "<a href=\"javascript:void(0);\" onclick=\"loadDoc('".$slide['slide_ori_id']."')\"><i class=\"fa fa-circle fa-fw\" aria-hidden=\"true\" ".$style."></i></a>&nbsp;";
			
#			$out_block .= "<i class=\"fa fa-info fa-1x\"></i> ";
			
			if ($_SESSION['user_data']['perm'] > 1) {
				
				
				$out_block .= "<a href=\"recordare.php?action=add_slide&slide_id=".$slide['slide_ori_id']."\">".$slide['slide_name']."</a>";
				
			} else {
				
				$out_block .= $slide['slide_name'];
				
			}
			$out_block .= "</td>";
			$out_block .= "<td style=\"background-color:#".$colors[0]."; color:#".$colors[1].";\">".$slide['gene_name']."<br />SB ".$slide['solution_book']."</td>";
			
			$cut_date = $this->format_date($slide['cut_date']);
			
			$cut_date_stamp = strtotime($cut_date);
			
			$diff = $experiment_date_stamp - $cut_date_stamp;
			
			$aday = 60 * 60 * 24;
			
			if ($diff <= $aday) {
				$diff_msg = "same day";
			} else {
				$diff_msg = ($diff/$aday)." days";
			}
			
			$days_btween = ($experiment_date_stamp - $cut_date_stamp)/(60*60*24);
			
#			$exp_date = new DateTime($cut_date);
			
#			echo "<pre>";
#			var_dump($cut_date_stamp);
#			echo "</pre>";
			
	#		$time_diff = $this->date_obj->diff($exp_date);
			
			$out_block .= "<td>";
			$out_block .= $this->format_date($slide['cut_date']);
			$out_block .= "<br />".$diff_msg;
			$out_block .= "</td>";
			
			$out_block .= "<td>";
			$out_block .= $slide['slide_status']."<br />";
			if ($slide['slide_status'] == "fail") {
				$out_block .= " (0/5)";
			} else {
				$out_block .= "(".sprintf("%.2f",$slide['slide_score'])."/5)";
			}
			$out_block .= "</td>";
			$out_block .= "</tr>";
		}
	
		$out_block .= "</table>\n";
		
		
		return $out_block;
	}
	
	public function printSimpleTable () {
		// requires experiment_date to be loaded
#echo	count($this->slide_ids);
		$in = str_repeat("?, ", count($this->slide_ids)-1) . "?";
		$sql = "select embryo.name as embryo_name, embryo.id as embryo_id, embryo.age, slide.name as slide_name, slide.slide_position as slide_id, slide.id as slide_ori_id, slide.status as slide_status, gene.gene_name, gene.colour from slide inner join embryo on slide.embryo_id=embryo.id inner join gene on slide.gene=gene.id where slide.id in (".$in.") order by embryo.age asc";
		$prep = $this->db->prepare($sql);

		$prep->execute($this->slide_ids);


		$res = $prep->fetchAll(PDO::FETCH_ASSOC);
#		echo "<pre>";
#		print_r($res);
#		print_r($prep->errorInfo());
#		echo "</pre>";
		
		$out_block = "<table class=\"massive_table\">\n";
		$out_block .= "<tr><th>age</th><th>embryo</th><th>slide</th><th>probe</th><th>status</th></tr>\n";
	
		foreach ($res as $slide) {
			$colors = explode(".", $slide['colour']);
			$out_block .= "<tr>";
			$out_block .= "<td>".$slide['age']."</td>";
			$out_block .= "<td><a href=\"frontpage.php?opr=showem&name=".$slide['embryo_name']."\">".$slide['embryo_name']."</a></td>";
			if ($_SESSION['user_data']['perm'] > 1) {
				$out_block .= "<td><a href=\"recordare.php?action=add_slide&slide_id=".$slide['slide_ori_id']."\">".$slide['slide_name']."</a></td>";
			} else {
				$out_block .= "<td>".$slide['slide_name']."</td>";
			}
			$out_block .= "<td style=\"background-color:#".$colors[0]."; color:#".$colors[1].";\">".$slide['gene_name']."</td>";
			$out_block .= "<td>".$slide['slide_status']."</td>";
			$out_block .= "</tr>";
		}
	
		$out_block .= "</table>\n";
		
		
		return $out_block;
	}
	
	public function printRecorderTable () {

#echo	count($this->slide_ids);
		$in = str_repeat("?, ", count($this->slide_ids)-1) . "?";
		$sql = "select embryo.name as embryo_name, embryo.id as embryo_id, embryo.age, slide.name as slide_name, slide.slide_position as slide_pos, slide.id as slide_id, slide.status as slide_status from slide inner join embryo on slide.embryo_id=embryo.id where slide.id in (".$in.") order by embryo.age asc";
		$prep = $this->db->prepare($sql);

		$prep->execute($this->slide_ids);


		$res = $prep->fetchAll(PDO::FETCH_ASSOC);

		
		$sql_genes = "select id, gene_name, solution_book from gene order by solution_book asc";
		$gene_prep = $this->db->prepare($sql_genes);
		$gene_prep->execute();
		$genesArray = $gene_prep->fetchAll(PDO::FETCH_ASSOC);
#		echo "<pre>";
#		print_r($prep->errorInfo());
#echo "</pre>";
#echo "<pre>";
#print_r($res);
#echo "</pre>";
		
		$out_block = "<table class=\"massive_table\">\n";
		$out_block .= "<tr><th>age</th><th>embryo</th><th>slide</th><th>probe</th><th>status</th></tr>\n";
	
		foreach ($res as $slide) {
			$out_block .= "<tr>";
			$out_block .= "<td>".$slide['age']."</td>";
			$out_block .= "<td><a href=\"frontpage.php?opr=showem&name=".$slide['embryo_name']."\">".$slide['embryo_name']."</a></td>";
			if ($_SESSION['user_data']['perm'] > 1) {
				$out_block .= "<td><a href=\"recordare.php?action=add_slide&slide_id=".$slide['slide_id']."\">".$slide['slide_name']."</a></td>";
			} else {
				$out_block .= "<td>".$slide['slide_name']."</td>";
			}
			$out_block .= "<td>";
			$out_block .= "<select name=\"slide_".$slide['slide_id']."\">\n";
			foreach ($genesArray as $gene) {
				$out_block .= "<option value=\"".$gene['id']."\">".$gene['solution_book']." (".$gene['gene_name'].") </option>\n";
			}
			$out_block .= "</select>";
			$out_block .= "</td>";
			$out_block .= "<td>";
			$out_block .= "<select name=\"status_".$slide['slide_id']."\">\n";
			$out_block .= "<option value=\"wait\" selected=\"selected\">wait</option>\n";
			$out_block .= "<option value=\"ok\">ok</option>\n";
			$out_block .= "<option value=\"fail\">fail</option>\n";
			$out_block .= "</select>";
			$out_block .= "</td>";
			$out_block .= "</tr>";
		}
	
		$out_block .= "</table>\n";
		
		
		return $out_block;
	}
	
}

?>