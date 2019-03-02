<?php

/*
 * annotated-properties
 *
 * Copyright (C) 2019 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace SOFe\AnnotatedProperties;

use Generator;
use function array_map;
use function array_splice;
use function array_values;
use function assert;
use function count;
use function explode;
use function is_array;
use function is_string;
use function iterator_to_array;
use function ltrim;
use function rand;
use function rtrim;
use function strlen;
use function substr;
use function trim;

class Properties{
	public static function fromFile(string $path){
		return new Properties(new FileLineIterator($path));
	}

	public static function fromContents(string $contents){
		return new Properties(array_map(function(string $line){
			return rtrim($line, "\r\n");
		}, explode("\n", $contents)));
	}

	/** @var string */
	private $separator;
	/** @var string|null */
	private $comment;
	/** @var string|null */
	private $escape;

	private $randomPrefix = 0;
	private $randomKey = 0;
	private $map = [];
	private $order = [];

	public function __construct(iterable $input, string $separator = "=", ?string $comment = "#", ?string $escape = "\\"){
		$this->separator = $separator;
		$this->comment = $comment;
		$this->escape = $escape;

		$this->randomPrefix = $comment . rand() . $comment . rand() . "\$";
		foreach($input as $line){
			$trimmed = trim($line);
			if($trimmed === "" || StringUtils::startsWith($trimmed, $comment)){
				$key = $this->makeRandomKey();
				$this->order[] = $key;
				$this->map[$key] = $line;
				continue;
			}

			if($escape === null){
				$parts = explode($separator, $trimmed);
				$left = rtrim($parts[0]);
				$right = isset($parts[1]) ? ltrim($parts[1]) : true;
			}else{
				[$left, $right] = self::separateLine($trimmed, $separator, $escape);
			}

			if(!isset($this->map[$left])){
				$this->map[$left] = [];
			}
			$this->order[] = [$left, count($this->map[$left])];
			$this->map[$left][] = $right;
		}
	}

	private static function separateLine(string $trimmed, string $separator, string $escape){
		assert(strlen($escape) === 1);
		$output = "";
		for($i = 0; $i < strlen($trimmed); $i++){
			$output .= $trimmed{$i};
			if($trimmed{$i} === $escape){
				$i++;
			}
			if(StringUtils::substringAt($trimmed, $separator, $i)){
				return [rtrim(substr($output, 0, -1)), ltrim(substr($trimmed, $i + strlen($separator)))];
			}
		}
		return [$trimmed, true];
	}

	private function makeRandomKey() : string{
		return $this->randomPrefix . ($this->randomKey++);
	}

	public function get(string $key, ?string $default = null) : ?string{
		$ret = ($this->map[$key] ?? [$default])[0];
		return $ret === true ? null : $ret;
	}

	public function set(string $key, ?string $value = null, array $comments = []) : void{
		if(isset($this->map[$key])){
			if(count($this->map[$key]) === 1){
				$this->map[$key][0] = $value;
			}else{
				$this->map[$key] = [$value];
				foreach($this->order as $i => $pair){
					if(is_array($pair) && $pair[0] === $key && $pair[1] !== 0){
						unset($this->order[$i]);
					}
				}
				$this->order = array_values($this->order);
			}
		}else{
			$this->map[$key] = [$value];
			$this->order[] = [$key, 0];
		}
		if(!empty($comments)){
			$randoms = [];
			foreach($comments as $comment){
				$random = $this->makeRandomKey();
				$this->map[$random] = $this->comment . " " . $comment;
				$randoms[] = $random;
			}
			foreach($this->order as $i => $pair){
				if(is_array($pair) && $pair[0] === $key){
					array_splice($this->order, $i, 0, $randoms);
					break;
				}
			}
		}
	}

	// TODO implement multi-insertion
	// TODO implement deletion (with comment carrying)

	public function getData() : array{
		$data = [];
		foreach($this->map as $key => $value){
			if(StringUtils::startsWith($key, $this->randomPrefix)){
				continue;
			}
			$data[$key] = $value[0];
		}
		return $data;
	}

	public function getAllData() : array{
		$data = [];
		foreach($this->map as $key => $value){
			if(StringUtils::startsWith($key, $this->randomPrefix)){
				continue;
			}
			$data[$key] = $value;
		}
		return $data;
	}

	public function someHasKeyOnly() : bool{
		foreach($this->map as $key => $values){
			if(StringUtils::startsWith($key, $this->randomPrefix)){
				continue;
			}
			foreach($values as $value){
				if($value === true){
					return true;
				}
			}
		}
		return false;
	}

	public function onlyHasKeyOnly() : bool{
		foreach($this->map as $key => $values){
			if(StringUtils::startsWith($key, $this->randomPrefix)){
				continue;
			}
			foreach($values as $value){
				if($value !== true){
					return false;
				}
			}
		}
		return true;
	}

	public function emit(bool $wrapSeparator = false) : Generator{
		$separator = $wrapSeparator ? " {$this->separator} " : $this->separator;
		foreach($this->order as $key){
			if(is_string($key)){
				yield $this->map[$key];
			}else{
				$output = $key[0];
				$value = $this->map[$key[0]][$key[1]];
				if($value !== true){
					$output .= $separator . $value;
				}
				yield $output;
			}
		}
	}

	public function emitArray(bool $wrapSeparator = false) : array{
		return iterator_to_array($this->emit($wrapSeparator), false);
	}
}
