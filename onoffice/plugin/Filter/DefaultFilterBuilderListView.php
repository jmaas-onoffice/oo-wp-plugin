<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Favorites;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderListView
	implements DefaultFilterBuilder
{
	/** @var string */
	private $_pDataListView = null;

	/** @var array */
	private $_defaultFilter = array(
		'veroeffentlichen' => array(
			array('op' => '=', 'val' => 1),
		),
	);



	/**
	 *
	 * @param DataListView $pDataListView
	 *
	 */

	public function __construct(DataListView $pDataListView)
	{
		$this->_pDataListView = $pDataListView;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter()
	{
		$filter = $this->_defaultFilter;

		switch ($this->_pDataListView->getListType()) {
			case DataListView::LISTVIEW_TYPE_FAVORITES:
				$filter = $this->getFavoritesFilter();
				break;
			case DataListView::LISTVIEW_TYPE_REFERENCE:
				$filter = $this->getReferenceViewFilter();
				break;
		}

		return $filter;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getFavoritesFilter()
	{
		$ids = Favorites::getAllFavorizedIds();

		$filter = $this->_defaultFilter;
		$filter['Id'] =  array(
			array('op' => 'in', 'val' => $ids),
		);

		return $filter;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getReferenceViewFilter()
	{
		$filter = $this->_defaultFilter;
		$filter['referenz'] =  array(
			array('op' => '=', 'val' => 1),
		);

		return $filter;
	}
}