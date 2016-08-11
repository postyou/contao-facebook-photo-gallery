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

$GLOBALS['TL_DCA']['tl_content']['palettes']['facebook_photo_gallery'] = '{type_legend},type,headline;{facebookPhotoGalleryOverview_legend},showFacebookGalerieOverviewPage;{facebookPhotoGalleryContent_legend},facebookUserAlbums,refresh,size,imagemargin,perRow, perPage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'showFacebookGalerieOverviewPage';

$GLOBALS['TL_DCA']['tl_content']['subpalettes']['showFacebookGalerieOverviewPage'] = 'overviewPictureSize';

$GLOBALS['TL_DCA']['tl_content']['fields']['facebookUserAlbums'] = array(
	'label'     		=> &$GLOBALS['TL_LANG']['tl_content']['facebookUserAlbums'],
	'inputType' 		=> 'checkbox',
	'options_callback' 	=> array('FacebookPhotoGalleryEngine', 'getAllChannels'),
	'eval'				=> array(
								'mandatory'	=>	true,
								'multiple' 	=> 	true,
							),
	'sql'				=> "blob NULL"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['showFacebookGalerieOverviewPage'] = array(
	'label'     		=> &$GLOBALS['TL_LANG']['tl_content']['showFacebookGalerieOverviewPage'],
	'inputType' 		=> 'checkbox',
	'eval'				=> array('submitOnChange' => true),
	'sql'				=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['overviewPictureSize'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['size'],
	'exclude'                 => true,
	'inputType'               => 'imageSize',
	'options'                 => System::getImageSizes(),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
	'sql'                     => "varchar(64) NOT NULL default ''"
);