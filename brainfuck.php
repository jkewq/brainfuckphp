<?php

class BrainFuck {
	
	const MAX_STEPS = 1000000;
	
	private $_steps = 0;
	private $_ptr = 0;
	private $_codeTape = [];
	private $_dptr = 0;
	private $_dataTape = [];
	private $_outputTape = [];
	private $_indexLeftJump = [];
	private $_indexRightJump = [];
	
	/**
	 * Entry point of the logic
	 *
	 * @param string $code BrainFuck program code
	 * @return string output of the BrainFuck program execution
	 */
	public function __invoke(string $code): string {
		$this->_preprocessCode($code);
		$this->_indexingJumps();
		$this->_initDataTape();
		$this->_execution();
		return implode('', $this->_outputTape);
	}
	
	/**
	 * Preprocessing the BrainFuck code: tokenization, ignoring of whitespace and non-instruction characters
	 * 
	 * @param $code BrainFuck program code
	 * @return void
	 */
	private function _preprocessCode(string $code): void {
		$commands = ['>', '<', '+', '-', '.', ',', '[', ']'];
		for($i=0;$i<strlen($code);$i++) {
			$token = $code[$i];
			if (in_array($token, $commands)) {
				array_push($this->_codeTape, $token);
			}
		}
	}
	
	/**
	 * Indexing the jumping points
	 *
	 * @return void
	 */
	private function _indexingJumps(): void {
		$actualLevel = 0;
		$levelBegin = [];
		foreach($this->_codeTape as $ptr => $token) {
			if ($token == '[') {
				$actualLevel++;
				$levelBegin[$actualLevel] = $ptr;
			} elseif ($token == ']') {
				$this->_indexRightJump[$levelBegin[$actualLevel]] = $ptr + 1;
				$this->_indexLeftJump[$ptr] = $levelBegin[$actualLevel] + 1;
				$actualLevel--;
			}
		}
	}
	
	/**
	 * Initializing the data type (prefilling it with zeros)
	 *
	 * @return void
	 */
	private function _initDataTape(): void {
		for($i=0;$i<30000;$i++) {
			array_push($this->_dataTape, 0);	
		}
	}
	
	/**
	 * Executing the BrainFuck program and storing the output on the output tape
	 *
	 * @return void
	 */
	private function _execution(): void {
		$tapeEnd = count($this->_codeTape) - 1;
		do {
			$this->_steps++;
			if ($this->_steps == self::MAX_STEPS) {
				throw new Exception('Maximum steps reached');
				break;	
			}
			$oldPtr = null;
			$token = $this->_codeTape[$this->_ptr];
			switch ($token) {
				case '>':
					$this->_dptr++;
					//echo 'ptr=' . $this->_ptr . ': ' . $token . ' data pointer became to '. $this->_dptr . PHP_EOL;
					break;
				case '<':
					$this->_dptr--;
					//echo 'ptr=' . $this->_ptr . ': ' . $token . ' data pointer became to '. $this->_dptr . PHP_EOL;
					break;
				case '+':
					$this->_dataTape[$this->_dptr]++;
					//echo 'ptr=' . $this->_ptr . ': ' . $token . ' data at the place ' . $this->_dptr . ' became ' . $this->_dataTape[$this->_dptr] . PHP_EOL;
					break;
				case '-':
					$this->_dataTape[$this->_dptr]--;
					//echo 'ptr=' . $this->_ptr . ': ' . $token . ' data at the place ' . $this->_dptr . ' became ' . $this->_dataTape[$this->_dptr] . PHP_EOL;
					break;
				case '.':
					array_push($this->_outputTape, chr($this->_dataTape[$this->_dptr]));
					//echo 'ptr=' . $this->_ptr . ': ' . $token . ' byte ' . $this->_dataTape[$this->_dptr] . ' was added to the output tape' . PHP_EOL;
					break;
				case ',':
					// generates exception, thus we don't support interactivity at this moment
					throw new Exception('Interactivity not supported');
					break;
				case '[':
					if ($this->_dataTape[$this->_dptr] == 0) {
						$oldPtr = $this->_ptr;
						$this->_ptr = $this->_indexRightJump[$this->_ptr];
						if (is_null($this->_ptr)) {
							throw new Exception('Instruction pointer was set to null');	
						}
						//echo 'ptr=' . $oldPtr . ': ' . $token . ' instruction pointer was set to ' . $this->_ptr . ', as data on place ' . $this->_dptr . ' was zero' . PHP_EOL;
					} else {
						//echo 'ptr=' . $this->_ptr . ': ' . $token . ' instruction pointer was not changed, as data on place ' . $this->_dptr . ' was not zero' . PHP_EOL;
					}
					break;
				case ']':
					if ($this->_dataTape[$this->_dptr] != 0) {
						$oldPtr = $this->_ptr;
						$this->_ptr = $this->_indexLeftJump[$this->_ptr];	
						if (is_null($this->_ptr)) {
							throw new Exception('Instruction pointer was set to null');	
						}
						//echo 'ptr=' . $oldPtr . ': ' . $token . ' instruction pointer was set to ' . $this->_ptr . ', as data on place ' . $this->_dptr . ' was not zero' . PHP_EOL;
						continue;
					} else {
						//echo 'ptr=' . $this->_ptr . ': ' . $token . ' instruction pointer was not changed, as data on place ' . $this->_dptr . ' was zero' . PHP_EOL;
					}
					break;
			}
			if (is_null($oldPtr) === TRUE) {
				$this->_ptr++;
			}
		} while ($this->_ptr < $tapeEnd);
	}
	
}


$bf = new BrainFuck;
echo $bf('My First BrainFuck Program!!!!: ++++++++[>+++++++++>++++>+<<<-]>.<
++++[>+++++++<-]>+.+++++++..+++.>.<
++++++++.--------.+++.------.--------.>+.>++.!');
echo PHP_EOL;


