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
 
/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['facebook_photo_gallery'] = array(
	'tables' => array('tl_facebook_photo_gallery'),
	'icon'   => 'system/modules/facebook-photo-gallery/assets/img/icon.gif'
);


/**
 * Content elements
 */
 $GLOBALS['TL_CTE']['includes']['facebook_photo_gallery'] = 'ContentFacebookPhotoGallery';


/**
 * Register Cronjob
 */
$GLOBALS['TL_CRON']['minutely'][] = array('FacebookPhotoGalleryEngine', 'checkForUpdates');