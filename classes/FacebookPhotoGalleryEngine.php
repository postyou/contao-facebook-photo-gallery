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

class FacebookPhotoGalleryEngine extends \Backend {

	public function getAllChannels() {
		$arrForms = array();
		$objForms = $this->Database->execute("SELECT * FROM tl_facebook_photo_gallery ORDER BY title");
		while ($objForms->next())
		{   
			$arrForms[$objForms->id] = $objForms->title;
		}
			
		return $arrForms;
	}

	// public function getAvailableAlbums($dc) {
	// 	if ($dc->activeRecord->albumDisplayType == 'specificAlbums') {
	// 		$arrForms = array();
	// 		$objForms = $this->Database->execute("SELECT facebookPhotoAlbums FROM tl_facebook_photo_gallery WHERE id IN (".implode(',',serialize($dc->activeRecord->facebookUserAlbums)).") ORDER BY title");
	// 		while ($objForms->next())
	// 		{   
	// 			$arrForms[$objForms->id] = $objForms->title;
	// 		}

	// 		return $this->
	// 	}
	// }
	// 
	// 

	public function loadFacebookPhotoAlbumsForUser($dc) {

		if ($dc->activeRecord->facebookUser) {
			$url = 'https://graph.facebook.com/v2.6/'.$dc->activeRecord->facebookUser.'/albums/?fields=link,name&access_token='.$GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_id'].'|'.$GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_secret'];

			$ch = curl_init();
			// if ($header)
			// {
			// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			// }
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$albums = json_decode(curl_exec($ch), true)['data'];
			$options = array();

			if (isset($albums)) {
				foreach ($albums as $album) {
					$options[$album['id']] = $album['name'].'<a style="text-decoration:underline;" target="_blank" href="'.$album['link'].'">     (link)</a>';
				}
				return $options;
			}


		}

		return;
	}

	public function checkForUpdates()
	{
		global $GLOBALS;
		$facebookApi = true;
		$allActiveJobs = $this->Database->execute("SELECT * FROM tl_facebook_photo_gallery WHERE published = 1 ORDER BY lastUpdate;");

		while ($allActiveJobs->next())
		{
			if (isset($GLOBALS['TL_CONFIG']['facebook_photo_gallery_cache']))
			{
				$duration = $GLOBALS['TL_CONFIG']['facebook_photo_gallery_cache'];
			} else {
				$duration = 300;
			}

			if (time() >= $allActiveJobs->lastUpdate + $duration)
			{
				if ($facebookApi)
				{
					if (isset($GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_id']) && $GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_id'] != '' && isset($GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_secret']) && $GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_secret'] != '')
					{
						$cacheData = $this->loadCacheData($allActiveJobs->id);

						$albumIds = unserialize($allActiveJobs->facebookPhotoAlbums);
						$data = array();

						foreach ($albumIds as $albumId) {
							$lastAlbumUpdate = 0;
							if (isset($cacheData)) {
								foreach ($cacheData as $album) {
									if ($album->id === $albumId) {
										$lastAlbumUpdate = $album->timestamp_updated;
										break;
									}
								}
							}
							$data[] = $this->loadAlbumData($albumId, $lastAlbumUpdate);
						}

						if ($data[0] === false) {
							return;
						}

						if ($data['error']['code'] == 4)
						{
							$facebookApi = false;
							$this->log($GLOBALS['TL_LANG']['ERR']['maximumRate'], 'FacebookPhotoGalleryEngine checkForUpdates()',TL_ERROR);
						} else {
							if (count($data) > 0)
							{
								$this->parseDataToCache($data, $allActiveJobs->id);
							}
						}
						
					} else {
						$facebookApi = false;
						$this->log($GLOBALS['TL_LANG']['ERR']['noFacebookCredentials'], 'FacebookPhotoGalleryEngine checkForUpdates()',TL_ERROR);
					}
					
				}
						
					
				$this->Database->prepare("UPDATE tl_facebook_photo_gallery SET lastUpdate=". time() ." WHERE id=?")->execute($allActiveJobs->id);
    			$this->createNewVersion('tl_facebook_photo_gallery', $allActiveJobs->id);
			}
			
		}
		
	}
	
	private function parseDataToCache($data, $fileId)
	{
		$cacheLibrary = array();

		$count = 0;
		foreach($data as $item)
		{
			$cacheLibrary[$count]['name'] = $item['name'];
            $cacheLibrary[$count]['link'] = $item['link'];
            $cacheLibrary[$count]['photos'] = $item['photos'];
			$cacheLibrary[$count]['timestamp_created'] = strtotime($item['created_time']);
			$cacheLibrary[$count]['timestamp_updated'] = strtotime($item['updated_time']);
			$cacheLibrary[$count]['id'] = $item['id'];
			$count++;
		}
		$fp = fopen(TL_ROOT.'/system/modules/facebook-photo-gallery/cache/'.$fileId.'.json.cache', 'w');
		fwrite($fp, json_encode($cacheLibrary));
		fclose($fp);
	}

	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');
 
        if (strlen($this->Input->get('tid')))
        {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
            $this->redirect($this->getReferer());
        }
 
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_facebook_photo_gallery::published', 'alexf'))
        {
            return '';
        }
 
        $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];
 
        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }
 
        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }

    public function toggleVisibility($intId, $blnPublished)
	{
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_facebook_photo_gallery::published', 'alexf'))
		{
			$this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'FacebookPhotoGalleryEngine toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
		
		$this->createInitialVersion('tl_facebook_photo_gallery', $intId);
		
    	if (is_array($GLOBALS['TL_DCA']['tl_facebook_photo_gallery']['fields']['published']['save_callback']))
    	{
        	foreach ($GLOBALS['TL_DCA']['tl_facebook_photo_gallery']['fields']['published']['save_callback'] as $callback)
        	{
            	$this->import($callback[0]);
            	$blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
        	}
    	}
 
    	$this->Database->prepare("UPDATE tl_facebook_photo_gallery SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")->execute($intId);
    	$this->createNewVersion('tl_facebook_photo_gallery', $intId);
	}

	public function convertToSeconds($str, $obj)
	{
		return $str*60;
	}
	
	public function convertToMinutes($str, $obj)
	{
		return $str/60;
	}

	private function loadAlbumData($albumId, $lastUpdate) {
		$url = 'https://graph.facebook.com/v2.6/'.$albumId.'?fields=name,link,created_time,updated_time&access_token='.$GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_id'].'|'.$GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_secret'];
		$ch = $this->initCurl($url);
		$albumData = json_decode(curl_exec($ch), true);
		curl_close($ch);
	

		$url = 'https://graph.facebook.com/v2.6/'.$albumId.'/photos/?fields=images,source,link,height,width&access_token='.$GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_id'].'|'.$GLOBALS['TL_CONFIG']['facebook_photo_gallery_app_secret'];
		$ch = $this->initCurl($url);
		$photos = json_decode(curl_exec($ch), true)['data'];
		curl_close($ch);
		
		$photos = $this->loadImagesToLocalFileSystem($albumId, $photos, $albumData['updated_time'], $lastUpdate);
		
		$albumData['photos'] = $photos;
		
		return $albumData;
	}

	private function loadCacheData($fileId) {
		// $cacheData = array();
		$path = TL_ROOT.'/system/modules/facebook-photo-gallery/cache/'.$fileId.'.json.cache';
		if (!file_exists($path)) {
			return;
		}
		$fp = fopen($path, 'r');
		$cacheData= fread($fp, filesize($path));
		fclose($fp);

		return json_decode($cacheData);
	}

	private function loadImagesToLocalFileSystem($albumId, $photos, $albumDataUpdatedTime, $lastUpdate) {
		$count = 0;
		foreach ($photos as &$photo) {
			$maxDim = 0;
			$maxImageSrc;
			foreach ($photo['images'] as $image) {
				if ($image['width'] >= $image['height']) {
					if ($image['width'] > $maxDim) {
						$maxDim = $image['width'];
						$maxImageSrc = $image['source'];
					}
				} else {
					if ($image['height'] > $maxDim) {
						$maxDim = $image['height'];
						$maxImageSrc = $image['source'];
					}
				}
			}

			$fileNameCount = $count;
			if ($count < 10) {
				$fileNameCount = "0".$count;
			}
			$path = TL_ROOT.'/files/facebook_photo_albums/'.$albumId.'/album_photo_'.$fileNameCount.$this->getFileTypeFromURL($maxImageSrc);

			if (strtotime($albumDataUpdatedTime) > intval($lastUpdate)) {

				$ch = $this->initCurl($maxImageSrc);

				if (!file_exists(TL_ROOT.'/files/facebook_photo_albums')) {
					mkdir(TL_ROOT.'/files/facebook_photo_albums');
				}
				if (!file_exists(TL_ROOT.'/files/facebook_photo_albums/'.$albumId)) {
					mkdir(TL_ROOT.'/files/facebook_photo_albums/'.$albumId);
				}

				
				if (file_exists($path)) {
					unlink($path);
				}

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$data = curl_exec($ch);
				curl_close($ch);

				$fp = fopen($path, 'w');
				fwrite($fp, $data);
				fclose($fp);
			}
			$photo['singleSRC'] = substr($path,strpos($path, '/files'));
			$count++;
		}
		return $photos;
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

	private function getFileTypeFromURL($url) {
		return substr($url, strripos($url, '.'), 4);
	}
}