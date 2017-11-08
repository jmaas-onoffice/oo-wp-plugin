<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;
use onOffice\WPlugin\Helper;

/**
 *
 */

class ContentFilter
{
	/**
	 *
	 */

	public function addCustomRewriteTags() {
		add_rewrite_tag('%estate_id%', '([^&]+)');
		add_rewrite_tag('%view%', '([^&]+)');
	}


	/**
	 *
	 */

	public function addCustomRewriteRules() {
		$pDetailView = DataView\DataDetailViewHandler::getDetailView();
		$detailPageId = $pDetailView->getPageId();

		if ($detailPageId != null) {
			$pagename = get_page_uri( $detailPageId );
			$pageUrl = $this->rebuildSlugTaxonomy($detailPageId);
			add_rewrite_rule( '^('.preg_quote( $pageUrl ).')/([0-9]+)/?$',
				'index.php?pagename='.urlencode( $pagename ).'&view=$matches[1]&estate_id=$matches[2]','top' );
		}
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return type
	 *
	 */

	public function registerEstateShortCodes( $attributesInput )
	{
		global $wp_query;
		$page = 1;
		if ( ! empty( $wp_query->query_vars['page'] ) ) {
			$page = $wp_query->query_vars['page'];
		}

		$attributes = shortcode_atts(array(
			'view' => null,
		), $attributesInput);

		if ($attributes['view'] !== null) {
			try {
				$pDetailView = DataView\DataDetailViewHandler::getDetailView();

				if ($pDetailView->getName() === $attributes['view'])
				{
					$pTemplate = new Template($pDetailView->getTemplate(), 'estate', 'default_detail');
					$pEstateDetail = $this->preloadSingleEstate($pDetailView);
					$pTemplate->setEstateList($pEstateDetail);
					$result = $pTemplate->render();
					return $result;
				}

				$pListViewFactory = new DataView\DataListViewFactory();
				$pListView = $pListViewFactory->getListViewByName($attributes['view']);

				if (is_object($pListView) && $pListView->getName() === $attributes['view'])
				{
					$pTemplate = new Template($pListView->getTemplate(), 'estate', 'default');
					$pEstateList = new EstateList($pListView);
					$pEstateList->loadEstates($page);
					$pTemplate->setEstateList($pEstateList);
					$result = $pTemplate->render();
					return $result;
				}
			} catch (\Exception $pException) {
				return $this->logErrorAndDisplayMessage($pException);
			}
			return __('Estates view not found.', 'onoffice');
		}
	}


	/**
	 *
	 * @param int $page
	 * @return string
	 *
	 */

	private function rebuildSlugTaxonomy( $page ) {
		$pPost = get_post( $page );

		if ($pPost === null) {
			return;
		}

		$listpermalink = $pPost->post_name;
		$parent = wp_get_post_parent_id( $page );

		if ( $parent ) {
			$listpermalink = $this->rebuildSlugTaxonomy( $parent ).'/'.$listpermalink;
		}

		return $listpermalink;
	}


	/**
	 *
	 * Filter the user's written page
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	public function filter_the_content( $content ) {
		$content = $this->filterForms( $content );
		return $content;
	}


	/**
	 *
	 * @param string $content
	 * @return string
	 *
	 */

	private function filterForms( $content ) {
		$regexSearch = '!(?P<tag>\[oo_form\s+(?P<name>[0-9a-z]+)?\])!';
		$matches = array();
		$pLanguage = new Language();
		preg_match_all( $regexSearch, $content, $matches );

		$matchescount = count( $matches ) - 1;
		$formConfig = ConfigWrapper::getInstance()->getConfigByKey( 'forms' );

		if ( 0 == $matchescount || empty( $matches['name'] ) ) {
			return $content;
		}
		$onofficeTags = $matches['name'];

		foreach ( $onofficeTags as $id => $name ) {
			if ( array_key_exists( $name, $formConfig ) ) {
				$language = $pLanguage->getLanguageForForm($name);
				$pTemplate = new Template( $name, 'form', 'defaultform' );
				$pForm = new \onOffice\WPlugin\Form( $name, $language );
				$pTemplate->setForm( $pForm );
				$htmlOutput = $pTemplate->render();

				$content = str_replace( $matches['tag'][$id], $htmlOutput, $content );
			}
		}

		return $content;
	}


	/**
	 *
	 * @param \Exception $pException
	 * @return string
	 *
	 */

	public function logErrorAndDisplayMessage( \Exception $pException ) {
		$output = '';

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$output = '<pre>'
					. '<u><strong>[onOffice-Plugin]</strong> Ein Fehler ist aufgetreten:</u><p>'
					.esc_html((string) $pException).'</pre></p>';
		}

		error_log('[onOffice-Plugin]: '.strval($pException));

		return $output;
	}


	/**
	 *
	 * @global \WP_Query $wp_query
	 * @param \onOffice\WPlugin\DataView\DataDetailView $pDetailView
	 * @return \onOffice\WPlugin\EstateDetail
	 *
	 */

	private function preloadSingleEstate(DataView\DataDetailView $pDetailView) {
		global $wp_query;

		$estateId = 0;
		if ( ! empty( $wp_query->query_vars['estate_id'] ) ) {
			$estateId = $wp_query->query_vars['estate_id'];
		}

		$pEstateList = new EstateDetail($pDetailView);
		$pEstateList->loadSingleEstate($estateId);

		return $pEstateList;
	}


	/**
	 *
	 * @global \WP_Query $wp_query
	 * @global \WP_Post $post
	 * @param \WP_Post[] $posts
	 * @return \WP_Post
	 *
	 */

	public function filter_the_posts( $posts ) {
		global $wp_query;

		if ( empty( $posts[0] ) ||
			empty( $wp_query->query_vars['pagename'] ) ) {
			return $posts;
		}

		$pHelper = new Helper();
		$oldPageId = $pHelper->get_pageId_by_title($wp_query->query_vars['pagename']);
		$view = null;


		if ( isset( $wp_query->query_vars['view'] ) ) {
			$view = $wp_query->query_vars['view'];
			$pageId = $this->getDetailViewPageidByListPageId( $oldPageId, $view );
		} else {
			return $posts;
		}

		if ( ! is_null( $view ) && ! is_null( $pageId ) &&
			! empty( $wp_query->query_vars['estate_id'] ) ) {
			$newPost = get_post( $pageId );
			remove_filter ('the_content', 'wpautop');

			if ( ! is_null( $newPost ) ) {
				$post = $newPost;
				$post->post_content = $this->filter_the_content( $post->post_content );
				$post->post_status = 'publish';
				return array($post);
			}
		}

		return $posts;
	}


	/**
	 *
	 * @param int $listPageid
	 * @param string $detailViewName
	 * @return int
	 *
	 */

	private function getDetailViewPageidByListPageId( $listPageid, $detailViewName ) {
		$estateConfig = ConfigWrapper::getInstance()->getConfigByKey( 'estate' );
		$configKey = $this->getConfigKeyByPostId( $listPageid );

		if (isset($estateConfig[$configKey]['views'][$detailViewName])) {
			return $estateConfig[$configKey]['views'][$detailViewName]['pageid'];
		}
	}


	/**
	 *
	 * @param int $pageid
	 * @return string
	 *
	 */

	private function getConfigKeyByPostId( $pageid ) {
		foreach ( ConfigWrapper::getInstance()->getConfigByKey( 'estate' ) as $index => $config ) {
			foreach ($config['views'] as $view) {
				if ( is_array( $view ) && array_key_exists( 'pageid', $view ) &&
					$view['pageid'] === $pageid ) {
					return $index;
				}
			}
		}

		return null;
	}


	/**
	 *
	 */

	public function registerScripts() {
		wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js' );
		wp_register_script( 'gmapsinit', plugins_url( '/js/gmapsinit.js', __DIR__ ), array('google-maps') );
		wp_register_script( 'jquery-latest', 'https://code.jquery.com/jquery-latest.js');
		wp_register_script( 'onoffice-favorites', plugins_url( '/js/favorites.js', ONOFFICE_PLUGIN_DIR.'/index.php' ));
	}


	/**
	 *
	 */

	public function includeScripts() {
		wp_enqueue_script( 'gmapsinit' );

		if ( is_file( plugin_dir_path( __FILE__ ).'../templates/default/style.css' ) ) {
			wp_enqueue_style( 'onoffice-template-style.css', $this->getFileUrl( 'style.css' ) );
		}

		if ( is_file( plugin_dir_path( __FILE__ ).'../templates/default/script.js' ) ) {
			wp_enqueue_style( 'onoffice-template-script.js', $this->getFileUrl( 'script.js' ) );
		}

		if (Favorites::isFavorizationEnabled()) {
			wp_enqueue_script( 'onoffice-favorites' );
		}

		wp_enqueue_script('jquery-latest');
	}


	/**
	 *
	 * @param string $fileName
	 * @return string
	 *
	 */

	private function getFileUrl( $fileName ) {
		return plugins_url( 'onoffice/templates/default/'. $fileName );
	}
}
