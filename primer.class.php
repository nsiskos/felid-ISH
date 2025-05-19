<?php

#class primer extends sequence {
class primer {

	private $db;
	public $primer_id;
#	public $ncbi;
	
	public $primer_info = array();
	
	public function __construct ($db, $primer_id) 
	{
		
#		parent :: __construct($ncbi);
		
		$this->db = $db;
		$this->primer_id = $primer_id;
#		$this->ncbi = $ncbi;
		
		$sql = "select * from primer_lib where lab_id=?";
		$primers_ret = $db->prepare($sql);
		$primers_ret->bindValue(1, $primer_id, PDO::PARAM_INT);
		$primers_ret->execute();
		$primers = $primers_ret->fetch(PDO::FETCH_ASSOC);
		
		$this->primer_info['tail'] = $primers['tail'];
		$this->primer_info['complementary'] = $primers['complementary'];
		$this->primer_info['re_site'] = $primers['re_site'];
		$this->primer_info['orientation'] = $primers['orientation'];
		
		$borders = explode("_", $primers['from_to']);
		$this->primer_info['from'] = $borders[0];
		$this->primer_info['to'] = $borders[1];
		
		$this->primer_info['template'] = $primers['template'];
		$this->primer_info['comments'] = $primers['comments'];
		
		$this->primer_info['manufact_tm'] = $primers['manufact_tm'];
		$this->primer_info['amplifix_tm'] = $primers['amplifix_tm'];
		
		$this->primer_info['ncbi'] = $primers['ncbi_pattern'];
		
		unset($primers);
		unset($db);	
	}
	
}

class primer_pair_product {
	
	public $primerF;
	public $primerR;
	public $ncbi;
	
	public $sequence;
	
#	public $interval;
	
	public function setPrimer ($from, $end, $tail, $ncbi) {
		
		$primer = array('from'=>$from, 'to'=>$end, 'tail'=>$tail, 'ncbi'=>$ncbi);

		$this->ncbi = $ncbi;
		
		if (empty($this->primerF)) {
			$this->primerF = $primer;
		} else {
			
			if ($this->ncbi != $primer['ncbi']) {
				echo "init fail: different ncbi!";
				$this->ncbi = FALSE;
				die;
			} else {
				
				if ($this->primerF['from'] > $from) {
					$this->primerR = $this->primerF;
					$this->primerF = $primer;
				} else {
					$this->primerR = $primer;
				}
				
			}
			
			
		}
		
		
	}
	
	public function setSeq ($seq) {
		$this->sequence = $this->primerF['tail'].$seq.$this->reverse_complement($this->primerR['tail']);
	}
	
#	public function getSeq () {
#		$seqeunce = new sequence($this->ncbi, "PHP_GET");
#		return $this->primerF['tail'].$sequence->getChunk($this->primerF['from'], $this->primerR['to']).$this->primerR['tail'];
#	}
	
	public function interval() {
		return [$this->primerF['from'], $this->primerR['to']];
	}
	
	public function getBasic () {
		return $this->primerF['from']."-".$this->primerR['to'];
	}
	
	
	public function reverse_complement ($input_string) {
		$substit = array( "A" => "T", 
			"a" => "t", 
			"T" => "A", 
			"t" => "a",
			"G" => "C",
			"g" => "c",
			"C" => "G",
			"c" => "g",
			"u" => "a",
			"U" => "A",
			"r" => "y",
			"R" => "Y",
			"y" => "r",
			"Y" => "R",
			"k" => "m",
			"K" => "M",
			"m" => "k",
			"M" => "K",
			"b" => "v",
			"B" => "V",
			"v" => "b",
			"V" => "B",
			"d" => "h",
			"D" => "H",
			"h" => "d",
			"H" => "D"
		);
		return strrev(strtr($input_string, $substit));
	}
	
}

class geneTable {
	
	private $db;
	
	public $table;
	
	public function __construct ($db)
	{
		$this->db = $db;
		
		$display_block = "";
		
		$sql = "select distinct(intended_targene) from primer_lib order by intended_targene";
		$genes_h = $this->db->prepare($sql);
		$genes_h->execute();
		$genes = $genes_h->fetchAll(PDO::FETCH_COLUMN, 0);
	
		$step = 6;
		$how_many_genes = count($genes);
		$height = ceil($how_many_genes/$step);
	
	//	$height = (($how_many_genes % $step) == 0) ? $rows_initial : $rows_initial+1 ;

	//echo $height;	
		$display_block .= "<table id=\"slidetable\">\n";
		$display_block .= "<tr>";
	
	//	echo $step*$height;
	
		for ($i=0;$i<($step*$height);++$i) {
			if ($i<$how_many_genes) {
				$display_block .= "<td><a href=\"manage_primers.php?opr=".$genes[$i]."\">".$genes[$i]."</a></td>";
				if ( (($i+1) % $step) == 0) {
					$display_block .= "</tr>\n<tr>";
				}
			} else {
				$display_block .= "<td></td>";
			}
		}
	
		$display_block .= "</tr>";
		$display_block .= "</table>";
		
		$this->table = $display_block;
	}
	
}

?>