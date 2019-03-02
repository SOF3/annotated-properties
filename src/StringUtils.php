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

use function strlen;
use function substr;

class StringUtils{
	public static function substringAt(string $string, string $substring, int $at) : bool{
		return substr($string, $at, strlen($substring)) === $substring;
	}

	public static function startsWith(string $string, string $prefix) : bool{
		return substr($string, 0, strlen($prefix)) === $prefix;
	}

	public static function endsWith(string $string, string $suffix) : bool{
		return substr($string, -strlen($suffix)) === $suffix;
	}
}
