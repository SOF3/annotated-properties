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

use Iterator;
use function fgets;
use function fopen;
use function fseek;

class FileLineIterator implements Iterator{
	private $fh;
	private $line;
	private $lineNo;

	public function __construct(string $path){
		$this->fh = fopen($path, "rb");
	}

	public function rewind(){
		fseek($this->fh, 0);
		$this->lineNo = 1;
	}

	public function current(){
		return $this->line;
	}

	public function next(){
		$this->line = fgets($this->fh);
		$this->lineNo++;
		if($this->line === "" || $this->line === false){
			$this->line = null;
		}
	}

	public function key(){
		return $this->lineNo;
	}

	public function valid(){
		return $this->line !== null;
	}
}
