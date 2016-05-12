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

$GLOBALS['TL_DCA']['tl_content']['palettes']['facebook_photo_gallery'] = '{type_legend},type,headline;{facebookPhotoGalleryContent_legend},facebookUserAlbums,refresh,size,imagemargin,perRow;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

// $GLOBALS['TL_DCA']['tl_content']['fields']['__selector__'][] = 'albumDisplayType';
// 

// $GLOBALS['TL_DCA']['tl_content']['subpalettes']['albumDisplayType'] = 'availableAlbums';

$GLOBALS['TL_DCA']['tl_content']['fields']['facebookUserAlbums'] = array(
	'label'     		=> &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['facebookUserAlbums'],
	'inputType' 		=> 'checkbox',
	'options_callback' 	=> array('FacebookPhotoGalleryEngine', 'getAllChannels'),
	'eval'				=> array(
								'mandatory'	=>	true,
								'multiple' 	=> 	true,
							),
	'sql'				=> "blob NULL"
);

// $GLOBALS['TL_DCA']['tl_content']['fields']['albumDisplayType'] = array(
// 	'inputType'	=> 'radio',
// 	'options'	=> array('allAlbums', 'specificAlbums'),
// 	'eval' => array('submitOnChange' => true),
// 	'sql'	=>	'varchar(28) NOT NULL default ""'
// );

// $GLOBALS['TL_DCA']['tl_content']['fields']['availableAlbums'] = array(
// 	'inputType' => 'checkbox',
// 	'options_callback' => array('FacebookPhotoGalleryEngine', 'getAvailableAlbums'),
// 	'eval' => array('multiple' => true),
// 	'sql' => 'BLOB NULL'
// );