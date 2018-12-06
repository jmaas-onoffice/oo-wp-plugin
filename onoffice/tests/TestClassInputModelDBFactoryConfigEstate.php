<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigBase;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryFilterableFields;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassInputModelDBFactoryConfigEstate
	extends WP_UnitTestCase
{
	/** @var array */
	private $_tableNames = [
		'oo_plugin_listviews',
		'oo_plugin_picturetypes',
		'oo_plugin_fieldconfig',
	];


	/**
	 *
	 */

	public function testGetConfig()
	{
		$pFactory = new InputModelDBFactoryConfigEstate();
		$config = $pFactory->getConfig();
		$this->assertGreaterThan(10, $config);

		$pReflectionInputModelDBFactory = new ReflectionClass(InputModelDBFactory::class);
		$constantsInputModelDBFactory = $pReflectionInputModelDBFactory->getConstants();

		$pReflectionInputModelDBFactoryFilterableFields = new ReflectionClass
			(InputModelDBFactoryFilterableFields::class);
		$constantsInputModelDBFactoryFilterable =
			$pReflectionInputModelDBFactoryFilterableFields->getConstants();

		foreach ($config as $key => $configEntry) {
			$this->assertTrue(in_array($key, $constantsInputModelDBFactory) ||
				in_array($key, $constantsInputModelDBFactoryFilterable));
			$this->assertTrue(in_array
				($configEntry[InputModelDBFactoryConfigBase::KEY_TABLE], $this->_tableNames));
			$this->assertContainsOnly
				('string', [$configEntry[InputModelDBFactoryConfigBase::KEY_FIELD]]);
		}
	}
}
