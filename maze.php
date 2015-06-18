<?php 
Class Maze
{
	public $width;
	public $height;
	public $maze;
	
	private $stack;
	
	public function Maze($width,$height){
		$this->width = $width;
		$this->height = $height;
		$this->maze = array();
		
		$this->stack = array();
		
		$this->generateBlank();
		$this->carveMaze();
		//var_dump($this->maze);
	}
	
	
	public function renderMaze(){
		for($y = 0; $y < $this->height; $y++){
			$row = array();
			for($x = 0; $x < $this->height; $x++){
				$cellType = $this->getCellValue(array($x,$y));
				switch($cellType){
					case 'solid' :
						$row[] = '||';
						break; 
					case 'empty' :
						$row[] = '  ';
						break;
				}
				
			}
			echo implode('',$row)."\n";
		}
	}
	
	private function generateBlank(){
		$row = array();
		for($x = 0; $x <= $this->width; $x++){
			$column = array();
			for($y = 0; $y <= $this->height; $y++){
					$column[] = 'solid';
			}
			$this->maze[] = $column;
		}
	}
	
	/*
	 *  Returns the string value stored at a given cell: 
	 *  'empty' and 'solid'
	 *  @param array $vector A given array containing the coordinates of a cell
	 *  @return string $cell The string value contained at that cell
	 */
	
	public function getCellValue($vector){
		$x = $vector[0];
		$y = $vector[1];
		
		$cell = $this->maze[$x][$y];
		return $cell;
	}
	
	/*
	 *  Sets the string value of a given cell.
	 *  The options are: 'empty' and 'solid'
	 *  
	 *  @param array $vector A given array containing the coordinates of a cell
	 *  @param string $type A valid sting of either of the above options to set the cell to  
	 *  default: 'solid'
	 */
	
	public function setCellValue($vector, $type = 'solid'){
		$this->ensureValidType($type);
		$x = $vector[0];
		$y = $vector[1];
		$this->maze[$x][$y] = $type;
	}
	
	/*
	 * checks a given string value against an array of valid types and throws an error if not found
	 * @param string $type String value to check against
	 * @return false if nothing found
	 */
	private function ensureValidType($type){
		switch ($type) {
			case 'solid':
				break;
			case 'empty':
				break;
			default:
				throw new Exception("setCellValue: $value is not a valid cell type");
				return false;
		}
	}
		
	/*
	 * picks a random cell from the maze
	 * @return array $point Containing the vector of the new point
	 * @todo refactor or delete this method
	 */
	
	private function pickRndCell(){
	
	
		$randX = mt_rand(0, $this->width-1);
	
		$randY = mt_rand(0, $this->height-1);
	
		$point = array($randX, $randY);
		return $point;
	}
	
	/*
	 * returns a random value of a given array
	 * @param array $choices An array from which to chose one
	 * @return mixed $choice A random value stored in the given array
	 * 
	 */
	private function getRndChoice($choices){
		$choice = $choices[array_rand($choices)];
		return $choice;
	}
	
	/*
	 * beginning at a random starting location,  carves out a maze using the (Recursive?) backtracker algorithm.
	 */
	private function carveMaze(){
		$initPoint = $this->pickRndCell();
		$this->stack[] = $initPoint;
		
		while ($this->stack ){
			$backtrack = false;
			$currentCell = $this->stack[0];
			$this->setCellValue($currentCell, 'empty');
			
			
			$perpendicularCells = $this->getNearPerpendicularCells($currentCell);
			$solidCells = $this->getCellsOfType($perpendicularCells,'solid'); // @TODO 1 first solid cell check might not be needed 
			
			if ($solidCells){ // @TODO 1
				
				$viableCells = $this->getViableCells($solidCells);

				if($viableCells){ //@TODO 1 refactor weird nested if statement
					$chosenCell = $this->getRndChoice($viableCells);
					
					array_unshift($this->stack, $chosenCell); //add the new cosen cell to the front of the stack
					
				}
				else{
					$backtrack = true;
				}
			}
			else{
				$backtrack = true;
			}
			
			if($backtrack) {
				#backtrack
				array_shift($this->stack); //deletes the first element of the stack 
			}
		}
	}
	
	
	
	/*
	 * checks an array of given cells and discards any that are bordered by empty cells
	 * 
	 * @param multi-dimensional array $candidates An array of vectors to be tested
	 * @return multi-dimensional array $viableCells An array of vectors that passed the test or false if none found
	 */
	
	private function getViableCells($candidates){
		$length = count($candidates);
		$viableCells = array();
		
		for ($i = 0; $i < $length; $i++){
			$candidate = $candidates[$i];
			$diagonalCells = $this->getNearDiagonalCells($candidate);
			$perpendicularCells = $this->getNearPerpendicularCells($candidate);
			
			$surroundingCells =  array_merge($diagonalCells, $perpendicularCells);
			$emptyDiagonalCells = $this->getCellsOfType($diagonalCells,'empty'); // @TODO refactor, because Diagonal cells will only be checked if there is one
			$solidCells = $this->getCellsOfType($surroundingCells,'solid');
			
			$solidCount = count($solidCells);
			if($solidCount>=6){
				
				$maxDiffBetweenOriginAndDiagonal = $this->getMaxDifference($this->stack[0], $emptyDiagonalCells[0]);
				if ($solidCount >= 7){
					$viableCells[] = $candidate;
				}
				else if( $maxDiffBetweenOriginAndDiagonal < 2){
					$viableCells[] = $candidate;
				}
			}
			//@TODO if continuing straight add candidate to list
		}
		
		if (count($viableCells)){
			return $viableCells;
		}
		
		else {
			return false;
		}
	}
	
	
	
	
	
	/*
	 * returns an array of adjecent cells of a given vector
	 * 
	 * @param array $vector A given array containing the coordinates of a cell
	 * @return multidimensional array $adjacentCells An array of the adjacent cells
	 */
	private function getNearPerpendicularCells($vector){
		$x = $vector[0];
		$y = $vector[1];
		$adjacentCells = array();
		if($x) //i.e not zero
			$adjacentCells[] = array($x-1,$y);
		if($y != $this->height-1) //i.e not greater than maze height
			$adjacentCells[] = array($x,$y+1);
		if($x != $this->width-1) //i.e not greater than maze width
			$adjacentCells[] = array($x+1,$y);
		if($y) //i.e not zero
			$adjacentCells[] = array($x,$y-1);
		
		return $adjacentCells;
	}
	
	
	/*
	 * returns an array of all the immediately surrounding cells of a given vector
	 *
	 * @param array $vector A given array containing the coordinates of a cell
	 * @return multidimensional array $surroundingCells An array of the surrounding cells
	 */
	private function getNearDiagonalCells($vector){
		$x = $vector[0];
		$y = $vector[1];
		
		$isNotOverRightOrBottomBorder = ($x != $this->width-1 AND $y != $this->height-1);
		$isNotOverRightOrTopBorder = ($x != $this->width-1 AND $y);
		$isNotOverLeftOrBottomBorder = ($x AND $y != $this->height-1);
		$isNotOverLeftOrTopBorder = ($x AND $y );
		
		if ($isNotOverLeftOrTopBorder){
			$diagonalCells[] = array($x-1,$y-1);
		}
		if ($isNotOverRightOrBottomBorder){
			$diagonalCells[] = array($x+1,$y+1);
		}
		if ($isNotOverRightOrTopBorder){
			$diagonalCells[] = array($x+1,$y-1);
		}
		if ($isNotOverLeftOrBottomBorder){
			$diagonalCells[] = array($x-1,$y+1);
		}
		return $diagonalCells;
	}
	
	/*
	 * Tests an array of cells discarding any not of a given cell type
	 * 
	 * @param array $candiates A given array of potential cell candiates 
	 * @param string $type The type of cells to select
	 * @return array $validCandidates An array of candiate cells that passed the test or
	 * false if none could be found
	 */
	
	private function getCellsOfType($candidates,$type){
		$this->ensureValidType($type);
		$length = count($candidates);
		$validCandidates = array();
		
		for($i = 0; $i < $length; $i++){
			$candidate = $candidates[$i];
			$candidateValue = $this->getCellValue($candidate);
			
			if($candidateValue === $type ) {
				$validCandidates[] = $candidate;
			}
		} //for
		
		if (count($validCandidates))
			return $validCandidates;
		else
			return false;
	}
	
	/*
	 * gets the largest coordinate difference between two given vectors
	 * @param array $vectorA 
	 * @param array $vectorb
	 * @return int $maxDifference The largest difference between vector A and B 
	 */
	
	private function getMaxDifference($vectorA, $vectorB){
		$A_x = $vectorA[0];
		$A_y = $vectorA[1];
		
		$B_x = $vectorB[0];
		$B_y = $vectorB[1];
		
		$maxDifference = array();
		
		$maxDifference[] = abs($A_x - $B_x);
		$maxDifference[] = abs($A_y - $B_y);
		
		//var_dump($maxDifference);
		return max($maxDifference);
	}
}

if (isset($argv)){
	$arguments = array_slice($argv, 1, count($argv) );
	if ($arguments){
		
		$maze = new Maze($arguments[0], $arguments[1]);
		$maze->renderMaze();
		//$methodName = array_shift(&$argV);
		//call_user_func_array(array($maze,$methodName), $argv);
	}
}
?>