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


$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{facebook_photo_gallery_legend:hide},facebook_photo_gallery_app_id,facebook_photo_gallery_app_secret';

$GLOBALS['TL_DCA']['tl_settings']['fields']['facebook_photo_gallery_app_id'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['facebook_photo_gallery_app_id'],
	'inputType'	=> 'text',
	'eval'		=> array('nospace'=>true, 'tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['facebook_photo_gallery_app_secret'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['facebook_photo_gallery_app_secret'],
	'inputType'	=> 'text',
	'eval'		=> array('nospace'=>true, 'tl_class'=>'w50 m12')
);
