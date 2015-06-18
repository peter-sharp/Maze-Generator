<?php 
require_once('maze.php');
class MazeTest extends PHPUnit_Framework_TestCase
{
	
	
	
	protected function setUp()
	{
		$this->testMaze = new Maze(20,20);
	}
	
	/*
	 * sets the permissions of a given method to public 
	 * Note: only works in php5(>=5.3.2)
	 * @param string $name The name of the method to set public 
	 * @return method $method the method set to public
	 */
	protected static function accessMethod($name,$classObj)
	{
		
		$class = new ReflectionClass(get_class($classObj));
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}
	
	
	public function testVectorExists()
	{
		
		$minCell = $this->testMaze->getCellValue(array(0,0));
		$this->assertInternalType('string',$minCell,$message = "Min cell not set $minCell" );
		$maxCell = $this->testMaze->getCellValue(array(19,19));
		$this->assertInternalType('string',$maxCell,$message = "Max cell not set $maxCell");
	}
	
	public function testCanpickRandomCell()
	{
		$testMaze = $this->testMaze;
		$pickRndPoint = self::accessMethod('pickRndCell',$testMaze);
		for($i = 0; $i < 4; $i++){
			$DefaultRandomPoint = $pickRndPoint->invokeArgs($testMaze, array());
			echo "\n" . implode(", ", $DefaultRandomPoint);
		}
	}
	
	public function testCanGenerate()
	{	
		
		$maxX = $this->testMaze->width;
		$maxY = $this->testMaze->height;
		$expectedEmptyCells = 20;
		$emptyCellCount = 0;
		for($x = 0; $x < $maxX; $x++){
			$cells = array();
			for($y = 0; $y < $maxY; $y++){
				$cell = $this->testMaze->getCellValue(array($x,$y));
				$cells[] = $cell;
				if ($cell === 'empty')
					$emptyCellCount++;
			}
			$cellArrayContents = implode(", ",$cells);
			$this->assertContains("solid",$cells,$message = "no solid tiles found on $x $y: $cellArrayContents");
			
		}
		
		
		$this->assertGreaterThan($expectedEmptyCells,$emptyCellCount,$message = "Less than $expectedEmptyCells empty cells found count: $emptyCellCount");
	}
	
	
}
?>