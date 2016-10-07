<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2014 Leo Feyer
 * 
 * @package   facebook-photo-gallery 
 * @author    Mario Gienapp
 * @license   MIT License
 * @copyright POSTYOU 2016
 */

namespace FacebookPhotoGallery;
 
class ContentFacebookPhotoGallery extends \ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_facebook_photo_gallery';


    public function __construct($objModule, $strColumn='main') {
        parent::__construct($objModule, $strColumn);
    }

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### FACEBOOK PHOTO ALBUM ###';
			$objTemplate->title = $this->headline;

			return $objTemplate->parse();
		}

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{

		
		$facebookUserAlbums = unserialize($this->facebookUserAlbums);
		$facebookUserAlbumData = array();
		

		foreach($facebookUserAlbums as $facebookUserAlbum)
		{
			if (file_exists(TL_ROOT.'/system/modules/facebook-photo-gallery/cache/'.$facebookUserAlbum.'.json.cache'))
			{

				$albums = $this->Database->execute('SELECT facebookPhotoAlbums FROM tl_facebook_photo_gallery_source WHERE id = '. $facebookUserAlbum);

				while ($albums->next()) {
					if ($this->showFacebookGalerieOverviewPage && \Input::get('id')) {
						$albumIds = array(\Input::get('id'));
					} else {
						$albumIds = unserialize($albums->facebookPhotoAlbums);
					}

					$cacheData = json_decode(file_get_contents(TL_ROOT.'/system/modules/facebook-photo-gallery/cache/'.$facebookUserAlbum.'.json.cache'), true);
					foreach ($albumIds as $albumId) {
						foreach ($cacheData as $data) {
							if ($data['id'] === $albumId) {
								$facebookUserAlbumData[] = $data;
							}
						}
					}
				}
			}
		}
		
		$timestamps = array();

		if (count($facebookUserAlbumData) > 0){
			foreach ($facebookUserAlbumData as $albumData)
			{
				$timestamps[] = $albumData['timestamp_updated'];
			}
		
			array_multisort($timestamps, SORT_DESC, $facebookUserAlbumData);
			
			$galleries = $this->parseGalleries($facebookUserAlbumData);
			if (!isset($galleries)) {
				$galleries = array();
			}

			$this->Template->galleries = $galleries;

		} else {
			$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['noFacebookGalleryData'];
		}
	}
	
	protected function parseGalleries($arrItems)
	{
		$limit = count($arrItems);

		if ($limit < 1)
		{
			return array();
		}

		$newArrItems = array();

		foreach($arrItems as $item)
		{
			$newArrItems[] = $this->parseGallery($item, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'));
		}

		return $newArrItems;
	}
	
	protected function parseGallery($arrItem,  $strClass='')
	{
		global $objPage;
		
		if ($arrItem != NULL) {
			
			$offset = 0;

			$objTemplate;
			if (\Input::get('id')) {
				$total = count($arrItem['photos']);
				$photos = array_values($arrItem['photos']);
				$objTemplate = $this->compileAlbumPageTemplate($photos, $total);
			} else {
				$objTemplate = $this->compileOverviewPageTemplate($arrItem['photos'][0]);
				$objTemplate->link = \Environment::get('requestUri').'?id='.$arrItem['id'];
			}
			

			$objTemplate->class = $strClass.' ce_gallery';
			$objTemplate->title = $arrItem['name'];
			$objTemplate->hl = $this->hl;
			return $objTemplate->parse();
		} else {
			return '';
		}
	}

	private function compileOverviewPageTemplate($photo) {
		$photo['size'] = $this->overviewPictureSize;
		// $photo['imagemargin'] = $this->imagemargin;
		
		$objTemplate = new \FrontendTemplate('facebook_photo_gallery_overview');
		$this->addImageToTemplate($objTemplate, $photo);
		return $objTemplate;
	}

	private function compileAlbumPageTemplate($photos, $limit) {
		$total = $limit;
		$photos = array_reverse($photos);


		$objPagination;
		// Pagination
			if ($this->perPage > 0)
			{
				// Get the current page
				$id = 'page_g' . $this->id;
				$page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

				// Do not index or cache the page if the page number is outside the range
				if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
				{
					/** @var \PageError404 $objHandler */
					$objHandler = new $GLOBALS['TL_PTY']['error_404']();
					$objHandler->generate($objPage->id);
				}

				// Set limit and offset
				$offset = ($page - 1) * $this->perPage;
				$limit = min($this->perPage + $offset, $total);

				$objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
						
				
			}

		$rowcount = 0;
		$colwidth = floor(100/$this->perRow);
		$intMaxWidth = (TL_MODE == 'BE') ? floor((640 / $this->perRow)) : floor((\Config::get('maxImageWidth') / $this->perRow));
		$strLightboxId = 'lightbox[lb' . $this->id . ']';
		$body = array();

			

		// Rows
		for ($i=$offset; $i<$limit; $i=($i+$this->perRow))
		{
			$class_tr = '';

			if ($rowcount == 0)
			{
				$class_tr .= ' row_first';
			}

			if (($i + $this->perRow) >= $limit)
			{
				$class_tr .= ' row_last';
			}

			$class_eo = (($rowcount % 2) == 0) ? ' even' : ' odd';

			// Columns
			for ($j=0; $j<$this->perRow; $j++)
			{
				$class_td = '';

				if ($j == 0)
				{
					$class_td .= ' col_first';
				}

				if ($j == ($this->perRow - 1))
				{
					$class_td .= ' col_last';
				}

				$objCell = new \stdClass();
				$key = 'row_' . $rowcount . $class_tr . $class_eo;

				// Empty cell
				if (!is_array($photos[($i+$j)]) || ($j+$i) >= $limit)
				{
					$objCell->colWidth = $colwidth . '%';
					$objCell->class = 'col_'.$j . $class_td;
				}
				else
				{
					// Add size and margin
					$photos[($i+$j)]['size'] = $this->size;
					$photos[($i+$j)]['imagemargin'] = $this->imagemargin;

					$this->addImageToTemplate($objCell, $photos[($i+$j)], $intMaxWidth, $strLightboxId);

					// Add column width and class
					$objCell->colWidth = $colwidth . '%';
					$objCell->class = 'col_'.$j . $class_td;
					$objCell->link = $photos[($i+$j)]['singleSRC'];
					$objCell->lightbox = 'data-lightbox="lb'.$arrItem['id'].'"';
				}

				$body[$key][$j] = $objCell;
			}

			++$rowcount;
		}

		$objTemplate = new \FrontendTemplate('facebook_photo_gallery_default');
		$objTemplate->setData($this->arrData);
		$objTemplate->pagination = $objPagination->generate("\n  ");
		$objTemplate->body = $body;
		$objTemplate->backLink = 'javascript:window.history.back();';
		$objTemplate->backText = $GLOBALS['TL_LANG']['tl_content']['backText'];

		return $objTemplate;
	}

	private function loadAlbumData($albumId) {
			$url = 'https://graph.facebook.com/v2.6/'.$albumId.'?fields=name&access_token='.$GLOBALS['TL_CONFIG']['aggregator_facebook_app_id'].'|'.$GLOBALS['TL_CONFIG']['aggregator_faceboook_app_secret'];
			$ch = $this->initCurl($url);
			$albumData = json_decode(curl_exec($ch), true);
			$url = 'https://graph.facebook.com/v2.6/'.$albumId.'/photos/?fields=images&access_token='.$GLOBALS['TL_CONFIG']['aggregator_facebook_app_id'].'|'.$GLOBALS['TL_CONFIG']['aggregator_faceboook_app_secret'];
			$ch = $this->initCurl($url);
			

			$photos = json_decode(curl_exec($ch), true)['data'];
			$albumData['photos'] = $photos;
			return $albumData;
	}

	private function initCurl($url) {
		$ch = curl_init();
		// if ($header)
		// {
		// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		// }
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		return $ch;
	}
}

