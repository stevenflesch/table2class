<?php
/*
Copyright 2008 Daniel Lubarov

>> LICENSE <<
This work is licensed under a Creative Commons Attribution 3.0 United States
License <http://creativecommons.org/licenses/by/3.0/us/>. If you use this
code within your own website or application, please provide a link back to
<http://indented.net/>.

>> USAGE EXAMPLE <<
$code_in = $_POST['code'];
$obj_code = new codeObject($code_in);
$obj_code->process();
$code_out = $obj_code->code;

*/

class codeObject {
	var $code;
	var $pad_paren_inside;
	var $pad_paren_after_construct;
	var $pad_paren_after_function;
	var $indentation_unit;

	function codeObject( $code_in          = "",
				$pad_paren_inside          = true,
				$pad_paren_after_keyword   = true,
				$pad_paren_after_function  = false,
				$indentation_unit          = "\t") {
		$this->code = $code_in;
		$this->pad_paren_inside = $pad_paren_inside;
		$this->pad_paren_after_keyword = $pad_paren_after_keyword;
		$this->pad_paren_after_function = $pad_paren_after_function;
		$this->indentation_unit = $indentation_unit;
		// Trim unneeded whitespace
		$this->code = trim($this->code);
		// Make new lines uniform so we can work with them easily
		$this->code = str_replace(array("\r\n", "\n", "\r"), "<<NEWLINE>>", $this->code);
		$this->code = str_replace("<<NEWLINE>>", "\n", $this->code);
	}

	function process() {
		$i = 0; // position of scanner within code string
		$level = 0; // indentation level
		$keywords = array('if', 'then', 'else', 'for', 'while', 'do', 'until', 'switch', 'and', 'or', 'xor', 'eor', '&&', '||');

		while ($i < strlen($this->code)) {
			$first_1 = substr($this->code, $i, 1); // first one character following scanner
			switch ($first_1) {
				case ' ': // whitespace
					$i ++;
					// Delete exess proceeding whitespace
					while (substr($this->code, $i, 1) == ' ') {
						$this->code = $this->str_delete(1, $this->code, $i);
					}
					continue 2;
					break;
				case "\n": // newline
					$i ++;
					// Indent unless this line is just a closing brace
					$first_solid_char = substr($this->code, $i);
					$first_solid_char = trim($first_solid_char);
					$first_solid_char = substr($first_solid_char, 0, 1);
					$this_level = $level;
					if ($first_solid_char == "}") {
						$this_level --;
					}
					// Indent new line
					$i += $this->indent($this_level, $i);
					// Erase preexisting indentation
					while (substr($this->code, $i, 1) == ' ' || substr($this->code, $i, 1) == "\t") {
						$this->code = $this->str_delete(1, $this->code, $i);
					}
					continue 2;
					break;
				case '\'': // single quote
				case '"': // double quote
					do {
						$i ++;
						$localchar = substr($this->code, $i, 1);
						if ($localchar == '\\') { // bcakslash
							// Escape proceeding character
							$i ++;
						}
						elseif ($localchar == $first_1) {
							// Active quote matched >> quote terminated
							$i ++;
							continue 3;
						}
					}
					while ($i < strlen($this->code));
					continue 2;
					break;
				case '(': // opening bracket
					// Left-hand padding
					$j = $i;
					do {
						$j --;
						$localchar = substr($this->code, $j, 1);
					}
					while (($localchar == ' ' || $localchar == "\n") && $j >= 0);
					do {
						$j --;
						$localchar = substr($this->code, $j, 1);
					}
					while ($localchar != ' ' && $localchar != "\n" && $j >= 0);
					$j ++;
					$prev_entity = substr($this->code, $j, $i - $j);
					$prev_entity = trim($prev_entity);
					$following_keyword = in_array($prev_entity, $keywords);
					if ($following_keyword) {
						$req_padding = $this->pad_paren_after_keyword;
					}
					else {
						$req_padding = $this->pad_paren_after_function;
					}
					$has_padding = (substr($this->code, $i - 1, 1) == ' ')? true : false;
					if ($req_padding && !$has_padding) {
						// Add padding
						$this->code = $this->str_insert(' ', $this->code, $i);
						$i ++;
					}
					elseif ($has_padding && !$req_padding) {
						// Get rid of padding
						$this->code = $this->str_delete(1, $this->code, $i - 1);
						$i --;
					}
					// Right-hand padding
					$req_padding = $this->pad_paren_inside;
					$has_padding = (substr($this->code, $i + 1, 1) == ' ')? true : false;
					if ($req_padding && !$has_padding) {
						// Add padding
						$this->code = $this->str_insert(' ', $this->code, $i + 1);
						$i ++;
					}
					elseif ($has_padding && !$req_padding) {
						// Get rid of padding
						$this->code = $this->str_delete(1, $this->code, $i + 1);
					}
					$level += 2;
					$i ++;
					continue 2;
					break;
				case ')': // closing bracket
					// Left-hand padding
					$req_padding = $this->pad_paren_inside;
					$has_padding = (substr($this->code, $i - 1, 1) == ' ')? true : false;
					if ($req_padding && !$has_padding) {
						// Add padding
						$this->code = $this->str_insert(' ', $this->code, $i);
						$i ++;
					}
					elseif ($has_padding && !$req_padding) {
						// Get rid of padding
						$this->code = $this->str_delete(1, $this->code, $i - 1);
						$i --;
					}
					$level -= 2;
					$i ++;
					continue 2;
					break;
				case '{': // opening brace
					$level ++;
					$i ++;
					continue 2;
					break;
				case '}': // closing brace
					$level --;
					$i ++;
					continue 2;
					break;
			}

			$first_2 = substr($this->code, $i, 2); // first two characters following scanner
			switch ($first_2) {
				case '/*': // multi-line comment
					// Skip to code after comment
					$end = strpos($this->code, '*/', $i);
					if ($end === false) {
						// Everything remaining is commented out >> we're done
						break 2;
					}
					$j = $i; // hold start posision
					$i = $end + 2; // end position
					while (true) {
						$j = strpos($this->code, "\n", $j);
						$j ++;
						if ($j === false || $j > $i) break;
						$ind = $this->indent($level, $j);
						$i += $ind;
						$j += $ind;
					}
					continue 2;
					break;
				case '//': // single-line comment
					// Catch comment
					$end = strpos($this->code, "\n", $i); // end of line
					if ($end === false) {
						// Everything remaining is commented out >> we're done
						break 2;
					}
					$i += 2; // skip the two slashes
					// Adjust comment spacing
					$comment_old = substr($this->code, $i, $end-$i);
					$comment_new = ' ' . trim($comment_old);
					// Replace old comment with new one
					$this->code = $this->str_delete(strlen($comment_old), $this->code, $i);
					$this->code = $this->str_insert($comment_new, $this->code, $i);
					// Jump to end of line
					$end = strpos($this->code, "\n", $i); // recalculate
					$i = $end;
					continue 2;
					break;
			}

			// Default mode: skip to next entity
			$i ++;
			while (ctype_alnum(substr($this->code, $i, 1))) {
				$i ++;
			}
		}
	}

	private function str_insert($insertstring, $intostring, $position) {
		$part1 = substr($intostring, 0, $position);
		$part2 = substr($intostring, $position);
		$part1 = $part1 . $insertstring;
		$whole = $part1 . $part2;
		return $whole;
	}

	private function str_delete($numchars, $fromstring, $position) {
		$part1 = substr($fromstring, 0, $position);
		$part2 = substr($fromstring, $position + $numchars);
		$whole = $part1 . $part2;
		return $whole;
	}

	function indent($level, $pos) {
		if ($level <= 0) return "";
		$indent = str_repeat($this->indentation_unit, $level);
		$this->code = $this->str_insert($indent, $this->code, $pos);
		return strlen($indent);
	}
}
?>