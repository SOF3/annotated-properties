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


use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase{
	public function testEmpty() : void{
		$properties = new Properties($input = []);
		Assert::assertEmpty($properties->getData());
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testSingleton() : void{
		$properties = new Properties($input = ["Foo"]);
		$data = $properties->getData();
		Assert::assertArrayHasKey("Foo", $data);
		Assert::assertEquals(true, $data["Foo"]);
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testKeyValuePair() : void{
		$properties = new Properties($input = ["Foo=Bar"]);
		$data = $properties->getData();
		Assert::assertArrayHasKey("Foo", $data);
		Assert::assertEquals("Bar", $data["Foo"]);
	}

	public function testSingletonCrossKeyValuePair() : void{
		$properties = new Properties($input = ["Foo=Bar", "Qux"]);
		$data = $properties->getData();
		Assert::assertTrue($data === ["Foo" => "Bar", "Qux" => true]);
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testKeyValuePairMixSingleton() : void{
		$properties = new Properties($input = ["Foo=Bar", "Foo"]);
		$data = $properties->getData();
		Assert::assertTrue($data === ["Foo" => "Bar"]);
		$allData = $properties->getAllData();
		Assert::assertTrue($allData === ["Foo" => ["Bar", true]]);
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testSingletonMixKeyValuePair() : void{
		$properties = new Properties($input = ["Foo", "Foo=Bar"]);
		$data = $properties->getData();
		Assert::assertTrue($data === ["Foo" => true]);
		$allData = $properties->getAllData();
		Assert::assertTrue($allData === ["Foo" => [true, "Bar"]]);
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testSpaces() : void{
		$properties = new Properties([" Foo = Bar "]);
		$data = $properties->getData();
		Assert::assertArrayHasKey("Foo", $data);
		Assert::assertEquals("Bar", $data["Foo"]);
		Assert::assertEquals(["Foo = Bar"], $properties->emitArray(true));
	}

	public function testComments() : void{
		$properties = new Properties(["# Foo = Bar "]);
		$data = $properties->getData();
		Assert::assertEmpty($data);
	}

	public function testMultiCharComments() : void{
		$properties = new Properties($input = ["// Foo = Bar "], false, "=", "//");
		$data = $properties->getData();
		Assert::assertEmpty($data);
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testNoCommentsInKey() : void{
		$properties = new Properties($input = [" Foo# = Bar "]);
		$data = $properties->getData();
		Assert::assertArrayHasKey("Foo#", $data);
		Assert::assertEquals("Bar", $data["Foo#"]);
		Assert::assertEquals(["Foo#=Bar"], $properties->emitArray());
	}

	public function testNoCommentsInValue() : void{
		$properties = new Properties($input = [" Foo = #Bar "]);
		$data = $properties->getData();
		Assert::assertArrayHasKey("Foo", $data);
		Assert::assertEquals("#Bar", $data["Foo"]);
		Assert::assertEquals(["Foo=#Bar"], $properties->emitArray());
	}

	public function testNoCommentsAfterValue() : void{
		$properties = new Properties($input = [" Foo = Bar#Qux "]);
		$data = $properties->getData();
		Assert::assertArrayHasKey("Foo", $data);
		Assert::assertEquals("Bar#Qux", $data["Foo"]);
		Assert::assertEquals(["Foo=Bar#Qux"], $properties->emitArray());
	}

	public function testKeyOnly() : void{
		$empty = new Properties([]);
		Assert::assertFalse($empty->anyIsSingleton());
		Assert::assertTrue($empty->allAreSingleton());

		$none = new Properties(["Foo", "Bar"]);
		Assert::assertTrue($none->anyIsSingleton());
		Assert::assertTrue($none->allAreSingleton());

		$some1 = new Properties(["Foo=Qux", "Bar"]);
		Assert::assertTrue($some1->anyIsSingleton());
		Assert::assertFalse($some1->allAreSingleton());

		$some2 = new Properties(["Foo", "Bar=Qux"]);
		Assert::assertTrue($some2->anyIsSingleton());
		Assert::assertFalse($some2->allAreSingleton());

		$all = new Properties(["Foo=Qux", "Bar=Qux"]);
		Assert::assertFalse($all->anyIsSingleton());
		Assert::assertFalse($all->allAreSingleton());
	}

	public function testCommentSingletonPairMix() : void{
		$properties = new Properties($input = ["#Foo=Bar", "Qux", "Corge=Grault", "#Foo", "Qux=Bar", "Corge"]);
		Assert::assertEquals($input, $properties->emitArray());
	}

	public function testCaseInsensitiveGet() : void{
		$properties = new Properties($input = ["FoO=Bar", "fOo=Qux"], true);
		Assert::assertEquals("Bar", $properties->get("foO"));
		Assert::assertEquals(["Bar", "Qux"], $properties->getAll("FOo"));
	}

	// TODO tests after insertion and deletion
}
