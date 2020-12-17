<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;
use function add_filter;
use function get_option;
use function update_option;
use function remove_filter;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\Installer\DatabaseChanges
 *
 * Please note $wpdb won't create any tables in testing mode
 *
 */

class TestClassDatabaseChanges
	extends WP_UnitTestCase
{
	/** amount of tables created */
	const NUM_NEW_TABLES = 7;

	/** @var string[] */
	private $_createQueries = [];

	/** @var string[] */
	private $_dropQueries = [];

	/** @var WPOptionWrapperTest */
	private $_pWpOption;

	/** @var DatabaseChanges */
	private $_pDbChanges;

	/**
	 * @before
	 */
	public function prepare()
	{
		global $wpdb;

		$this->_pWpOption = new WPOptionWrapperTest();
		$this->_pDbChanges = new DatabaseChanges($this->_pWpOption, $wpdb);

		add_option('onoffice-default-view', '');
		$dataSimilarViewOptions = new \onOffice\WPlugin\DataView\DataDetailView();
		$dataSimilarViewOptions->name = "onoffice-default-view";
		$dataSimilarViewOptions->setDataDetailViewActive(true);

		$dataViewSimilarEstates = $dataSimilarViewOptions->getDataViewSimilarEstates();
		$dataViewSimilarEstates->setSameEstateKind(true);
		$dataViewSimilarEstates->setSameMarketingMethod(true);
		$dataViewSimilarEstates->setSamePostalCode(true);
		$dataViewSimilarEstates->setRadius(35);
		$dataViewSimilarEstates->setRecordsPerPage(13);
		$dataViewSimilarEstates->setTemplate('/test/similar/template.php');
		$dataSimilarViewOptions->setDataViewSimilarEstates($dataViewSimilarEstates);
		update_option('onoffice-default-view', $dataSimilarViewOptions);
	}

	/**
	 * @covers \onOffice\WPlugin\Installer\DatabaseChanges::install
	 */

	public function testInstall(): array
	{
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->assertGreaterThanOrEqual(self::NUM_NEW_TABLES, count($this->_createQueries));

		$dbversion = $this->_pDbChanges->getDbVersion();
		$this->assertEquals(17, $dbversion);
		return $this->_createQueries;
	}

	public function testInstallMigrationsDataSimilarEstates(): array
	{
		add_option('oo_plugin_db_version', '16');
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		$this->_pDbChanges->install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$prefix = $this->_pDbChanges->getPWPDB()->prefix;
		$similarViewOptions = $this->_pDbChanges->getPWPDB()->get_results("select * from " . $prefix . "options where option_name = 'onoffice-similar-estates-settings-view'");
		$new_data = [];
		if(!empty($similarViewOptions)){
			foreach ($similarViewOptions as $similarViewOption) {
				$similarViewOptionValues = maybe_unserialize($similarViewOption->option_value);
				$new_data['enablesimilarestates'] = $similarViewOptionValues->getDataSimilarViewActive();

				$newDataViewSimilarEstates = $similarViewOptionValues->getDataViewSimilarEstates();
				$new_data['radius'] = $newDataViewSimilarEstates->getRadius();
				$new_data['same_kind'] = $newDataViewSimilarEstates->getSameEstateKind();
				$new_data['same_maketing_method'] = $newDataViewSimilarEstates->getSameMarketingMethod();
				$new_data['same_postal_code'] = $newDataViewSimilarEstates->getSamePostalCode();
				$new_data['amount'] = $newDataViewSimilarEstates->getRecordsPerPage();
				$new_data['similar_estates_template'] = $newDataViewSimilarEstates->getTemplate();
			}
		}
		$this->assertEquals($new_data['enablesimilarestates'], true);
		$this->assertEquals($new_data['same_kind'], true);
		$this->assertEquals($new_data['same_postal_code'], true);
		$this->assertEquals($new_data['same_maketing_method'], true);
		$this->assertEquals($new_data['radius'], 35);
		$this->assertEquals($new_data['amount'], 13);
		$this->assertEquals($new_data['similar_estates_template'], '/test/similar/template.php');

		return $this->_createQueries;
	}


	/**
	 *
	 */
	public function testMaxVersion()
	{
		$this->assertEquals(17, DatabaseChanges::MAX_VERSION);
	}


	/**
	 *
	 * @depends testInstall
	 * @param array $createQueries from testInstall test.
	 *
	 */

	public function testUninstall(array $createQueries)
	{
		add_filter('query', [$this, 'saveDropQuery'], 1);
		$this->_pDbChanges->deinstall();
		remove_filter('query', [$this, 'saveDropQuery'], 1);

		$this->assertGreaterThanOrEqual(self::NUM_NEW_TABLES, count($this->_dropQueries));

		// assert that as many tables have been removed as have been created
		$uniqueCreateQueries = array_unique($createQueries);
		$uniqueDropQueries = array_unique($this->_dropQueries);

		$this->assertEquals(count($uniqueCreateQueries), count($uniqueDropQueries));

		$dbversion = $this->_pWpOption->getOption('oo_plugin_db_version', null);
		$this->assertNull($dbversion);
		$this->assertNull($this->_pDbChanges->getDbVersion());
	}


	/**
	 *
	 * @param string $query
	 * @return string
	 *
	 */

	public function saveCreateQuery(string $query): string
	{
		if (__String::getNew($query)->startsWith('CREATE TABLE')) {
			$this->_createQueries []= $query;
		}

		return $query;
	}


	/**
	 *
	 * @param string $query
	 * @return string
	 *
	 */

	public function saveDropQuery(string $query): string
	{
		if (__String::getNew($query)->startsWith('DROP TABLE')) {
			$this->_dropQueries []= $query;
		}

		return $query;
	}
}
