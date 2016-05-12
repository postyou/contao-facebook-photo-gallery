<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2014 Leo Feyer
 * 
 * @package   facebook-photo-gallery 
 * @author    Mario Gienapp
 * @license   MIT License
 * @copyright POSTYOU Werbeagentur 2016
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
        // $GLOBALS['TL_CSS']['aggregator'] = 'system/modules/aggregator/assets/css/aggregator.css';
        // $GLOBALS['TL_CSS']['fontAwesome'] = 'system/modules/aggregator/assets/fonts/font-awesome/css/font-awesome.min.css';
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
		
		// $count = 0;

		// $albumIds = array();

		foreach($facebookUserAlbums as $facebookUserAlbum)
		{
			if (file_exists(TL_ROOT.'/system/modules/facebook-photo-gallery/cache/'.$facebookUserAlbum.'.json.cache'))
			{

				$albums = $this->Database->execute('SELECT facebookPhotoAlbums FROM tl_facebook_photo_gallery WHERE id = '. $facebookUserAlbum);

				while ($albums->next()) {
					$albumIds = unserialize($albums->facebookPhotoAlbums);

					$cacheData = json_decode(file_get_contents(TL_ROOT.'/system/modules/facebook-photo-gallery/cache/'.$facebookUserAlbum.'.json.cache'), true);
					foreach ($albumIds as $albumId) {
						foreach ($cacheData as $data) {
							if ($data['id'] === $albumId) {
								$facebookUserAlbumData[] = $data;
								// if ($count == 0)
								// {
								// 	$facebookUserAlbumData = $data;
								// 	// $albumIds = unserialize($albums);
								// } else {
								// 	$facebookUserAlbumData = array_merge($facebookUserAlbumData, $data);
								// 	// $albumIds = array_merge($albumIds, unserialize($albums));
								// }
							}
						}
					}
				}
				
				// $count++;
			}
		}
		
		$timestamps = array();

		if (count($facebookUserAlbumData) > 0){
			foreach ($facebookUserAlbumData as $albumData)
			{
				$timestamps[] = $albumData['timestamp_updated'];
			}
		
			array_multisort($timestamps, SORT_DESC, $facebookUserAlbumData);
			
			$galleries = $this->parseItems($facebookUserAlbumData);
			if (!isset($galleries)) {
				$galleries = array();
			}
// var_dump($galleries);

			$this->Template->galleries = $galleries;

		} else {
			$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['noFacebookGalleryData'];
		}
	}
	
	protected function parseItems($arrItems)
	{
		$limit = count($arrItems);

		if ($limit < 1)
		{
			return array();
		}

		$count = 0;
		$newArrItems = array();

		foreach($arrItems as $item)
		{
			$newArrItems[] = $this->parseItem($item, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'), $count);
		}

		return $newArrItems;
	}
	
	protected function parseItem($arrItem,  $strClass='', $intCount=0)
	{
		global $objPage;
		
		if ($arrItem != NULL) {
			
			$offset = 0;
			$total = count($arrItem['photos']);
			$limit = $total;

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
				$this->Template->pagination = $objPagination->generate("\n  ");
			}

			$rowcount = 0;
			$colwidth = floor(100/$this->perRow);
			$intMaxWidth = (TL_MODE == 'BE') ? floor((640 / $this->perRow)) : floor((\Config::get('maxImageWidth') / $this->perRow));
			$strLightboxId = 'lightbox[lb' . $this->id . ']';
			$body = array();

			$photos = array_values($arrItem['photos']);

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
						// $photos[($i+$j)]['fullsize'] = $this->fullsize;
						// var_dump(expression)

						// echo "<pre>";
						// var_dump($photos[($i+$j)]);
						// echo "</pre>";
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
			// $objTemplate->authorLink = $arrItem['author']['url'];
			// $objTemplate->authorPicture = $arrItem['author']['picture'];
			// $objTemplate->authorName = $arrItem['author']['name'];
			$objTemplate->class = $strClass.' ce_gallery';

			$objTemplate->title = $arrItem['name'];

			$objTemplate->body = $body;

			// $objTemplate->photos = $photos;

			// $objTemplate->date = \Date::parse($objPage->datimFormat, $arrItem['timestamp']);
			// $objTemplate->datetime = $objTemplate->datetime = date('Y-m-d\TH:i:sP', $arrItem['timestamp']);
			// $objTemplate->teaser = $arrItem['item']['message'];
			// $objTemplate->hasImage = $arrItem['item']['picture'] ? true : false;
			// $objTemplate->imgUrl = $arrItem['item']['picture'];
			// $objTemplate->imgAlt = substr($arrItem['item']['message'], 0, 24);
			// $objTemplate->more = sprintf('<a href="%s" target="_blank" title="%s">%s</a>', $arrItem['item']['url'], specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], substr($arrItem['item']['message'], 0, 12)), true), $GLOBALS['TL_LANG']['MSC']['more']);
			// $objTemplate->link = $arrItem['item']['url'];
			// $objTemplate->type = $arrItem['item']['type'];
			// $objTemplate->mediaLink = $arrItem['item']['link'];

			return $objTemplate->parse();
		} else {
			return '';
		}
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

