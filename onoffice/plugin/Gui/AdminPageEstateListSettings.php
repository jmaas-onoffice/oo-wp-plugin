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

namespace onOffice\WPlugin\Gui;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateListSettings;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Record\RecordManagerReadListView;
use stdClass;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateListSettings
	extends AdminPageEstateListSettingsBase
{
	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->setPageTitle(__('Edit List View', 'onoffice'));
	}


	/**
	 *
	 * @return bool
	 *
	 */

	protected function buildForms()
	{
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderEstateListSettings($this->getPageSlug());
		$pFormModel = $pFormModelBuilder->generate($this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);

		$pInputModelFilter = $pFormModelBuilder->createInputModelFilter();
		$pInputModelRecordsPerPage = $pFormModelBuilder->createInputModelRecordsPerPage();
		$pInputModelSortBy = $pFormModelBuilder->createInputModelSortBy();
		$pInputModelSortOrder = $pFormModelBuilder->createInputModelSortOrder();
		$pInputModelListType = $pFormModelBuilder->createInputModelListType();
		$pInputModelShowStatus = $pFormModelBuilder->createInputModelShowStatus();
		$pFormModelRecordsFilter = new FormModel();
		$pFormModelRecordsFilter->setPageSlug($this->getPageSlug());
		$pFormModelRecordsFilter->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelRecordsFilter->setLabel(__('Filters & Records', 'onoffice'));
		$pFormModelRecordsFilter->addInputModel($pInputModelFilter);
		$pFormModelRecordsFilter->addInputModel($pInputModelRecordsPerPage);
		$pFormModelRecordsFilter->addInputModel($pInputModelSortBy);
		$pFormModelRecordsFilter->addInputModel($pInputModelSortOrder);
		$pFormModelRecordsFilter->addInputModel($pInputModelListType);
		$pFormModelRecordsFilter->addInputModel($pInputModelShowStatus);
		$this->addFormModel($pFormModelRecordsFilter);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pInputModelDocumentTypes = $pFormModelBuilder->createInputModelExpose();
		$pFormModelDocumentTypes = new FormModel();
		$pFormModelDocumentTypes->setPageSlug($this->getPageSlug());
		$pFormModelDocumentTypes->setGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$pFormModelDocumentTypes->setLabel(__('Downloadable Documents', 'onoffice'));
		$pFormModelDocumentTypes->addInputModel($pInputModelDocumentTypes);
		$this->addFormModel($pFormModelDocumentTypes);

		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE);

		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ESTATE, $pFormModelBuilder, $fieldNames);
		$this->addSortableFieldsList(array(onOfficeSDK::MODULE_ESTATE), $pFormModelBuilder);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormRecordsFilter = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$this->createMetaBoxByForm($pFormRecordsFilter, 'normal');

		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'side');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');

		$pFormDocumentTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$this->createMetaBoxByForm($pFormDocumentTypes, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$fieldNames = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE));

		foreach ($fieldNames as $category)
		{
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}
	}


	/**
	 *
	 * Since checkbox are only being submitted if checked they need to be reorganized
	 * @todo Examine booleans automatically
	 *
	 * @param stdClass $values
	 *
	 */

	protected function prepareValues(stdClass $values) {
		$pInputModelFactory = new InputModelDBFactory(new InputModelDBFactoryConfigEstate());
		$pInputModelFilterable = $pInputModelFactory->create
			(InputModelDBFactoryConfigEstate::INPUT_FIELD_FILTERABLE, 'filterable', true);
		$identifierFilterable = $pInputModelFilterable->getIdentifier();
		$pInputModelFieldName = $pInputModelFactory->create
			(InputModelDBFactory::INPUT_FIELD_CONFIG, 'fields', true);
		$identifierFieldName = $pInputModelFieldName->getIdentifier();
		if (property_exists($values, $identifierFilterable) &&
			property_exists($values, $identifierFieldName)) {
			$fieldsArray = (array)$values->$identifierFieldName;
			$filterableFields = (array)$values->$identifierFilterable;
			$newFilterableFields = array_fill_keys(array_keys($fieldsArray), '0');

			foreach ($filterableFields as $requiredField) {
				$keyIndex = array_search($requiredField, $fieldsArray);
				$newFilterableFields[$keyIndex] = '1';
			}

			$values->$identifierFilterable = $newFilterableFields;
		}

		$pInputModelHidden = $pInputModelFactory->create
			(InputModelDBFactoryConfigEstate::INPUT_FIELD_HIDDEN, 'hidden', true);
		$identifierHidden = $pInputModelHidden->getIdentifier();

		if (property_exists($values, $identifierHidden) &&
			property_exists($values, $identifierFieldName)) {
			$fieldsArray = (array)$values->$identifierFieldName;
			$hiddenFields = (array)$values->$identifierHidden;
			$newHiddenFields = array_fill_keys(array_keys($fieldsArray), '0');

			foreach ($hiddenFields as $hiddenField) {
				$keyIndex = array_search($hiddenField, $fieldsArray);
				$newHiddenFields[$keyIndex] = '1';
			}

			$values->$identifierHidden = $newHiddenFields;
		}
	}


	/**
	 *
	 * @param int $recordId
	 * @throws UnknownViewException
	 *
	 */

	protected function validate($recordId = null)
	{
		if ($recordId == null) {
			return;
		}

		$pRecordReadManager = new RecordManagerReadListView();
		$values = $pRecordReadManager->getRowById($recordId);
		$pFactory = new DataListViewFactory();
		$pDataListView = $pFactory->createListViewByRow($values);

		if (!in_array($pDataListView->getListType(), array('default', 'reference', 'favorites'))) {
			throw new UnknownViewException;
		}
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		parent::doExtraEnqueues();

		wp_register_script('oo-checkbox-js',
			plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'/js/checkbox.js', array('jquery'), '', true);
		wp_enqueue_script('oo-checkbox-js');
	}
}
