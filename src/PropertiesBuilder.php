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

use function array_map;
use function explode;
use function rtrim;

class PropertiesBuilder{
	/** @var bool */
	private $caseInsensitive = false;
	/** @var string */
	private $separator = "=";
	/** @var string|null */
	private $comment = "#";
	/** @var string|null */
	private $escape = "\\";

	public function setCaseInsensitive(bool $caseInsensitive) : PropertiesBuilder{
		$this->caseInsensitive = $caseInsensitive;
		return $this;
	}

	public function setSeparator(string $separator) : PropertiesBuilder{
		$this->separator = $separator;
		return $this;
	}

	public function setComment(?string $comment) : PropertiesBuilder{
		$this->comment = $comment;
		return $this;
	}

	public function setEscape(?string $escape) : PropertiesBuilder{
		$this->escape = $escape;
		return $this;
	}

	public function build(iterable $input) : Properties{
		return new Properties($input, $this->caseInsensitive, $this->separator, $this->comment, $this->escape);
	}

	public function buildFromFile(string $path) : Properties{
		return $this->build(new FileLineIterator($path));
	}

	public function buildFromContents(string $contents) : Properties{
		return $this->build(array_map(function(string $line){
			return rtrim($line, "\r\n");
		}, explode("\n", $contents)));
	}
}
