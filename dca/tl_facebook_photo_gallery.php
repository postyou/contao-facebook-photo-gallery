<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2014 Leo Feyer
 * 
 * @package   aggregator 
 * @author    Johannes Terhürne
 * @license   MIT License
 * @copyright Johannes Terhürne 2014 
 */
 
/**
 * Table tl_aggregator
 */
$GLOBALS['TL_DCA']['tl_facebook_photo_gallery'] = array(
	'config'   => array(
		'dataContainer'    => 'Table',
		'enableVersioning' => true,
		'sql'              => array(
			'keys' => array(
				'id' => 'primary'
			)
		),
	),
	
	'list'     => array(
		'sorting'           => array(
			'mode'        => 2,
			'fields'      => array('title'),
			'flag'        => 1,
			'panelLayout' => 'filter;sort,search,limit'
		),
		
		'label'             => array(
			'fields' => array('type', 'title'),
			'format' => '[%s] %s',
		),
		
		'global_operations' => array(
			'all' => array(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		
		'operations'        => array(
			'edit'   => array(
				'label' => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),
			'delete' => array(
				'label'      => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show'   => array(
				'label'      => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['show'],
				'href'       => 'act=show',
				'icon'       => 'show.gif',
				'attributes' => 'style="margin-right:3px"'
			),
			'toggle'	=> array(
				'label'               => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['toggle'],
    			'icon'                => 'visible.gif',
    			'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
    			'button_callback'     => array('FacebookPhotoGalleryEngine', 'toggleIcon')
			),
		)
	),
	'palettes' => array(
		'default'       		=> '{title_legend},title,facebookUser;{source_legend}, facebookPhotoAlbums, published;',
	),
	
	'fields'   => array(
		'id'     => array(
			'sql' => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'lastUpdate'	=> array(
			'sql'	=> "int(10) unsigned NOT NULL default '0'"
		),
		'title'  => array(
			'label'     => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['title'],
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
            'search'    => true,
			'eval'      => array(
								'mandatory'				=> true,
                            	'unique'				=> false,
                            	'maxlength'   			=> 255,
								'tl_class'				=> 'clr',
 							),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'facebookUser'    => array(
			'label'         => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['facebookUser'],
			'inputType' => 'text',
			'search'	=> true,
			'exclude'     => true,
			'eval'      => array(
								'mandatory'   => true,
								'submitOnChange' => true
 							),
			'sql'            => "varchar(255) NOT NULL"
		),
		// 'loadAlbumsRequestButton'	=> array(
		// 	'input_field_callback' => array('tl_facebook_photo_gallery', 'addAlbumRequestButton')
		// ),
		'facebookPhotoAlbums' => array(
			'label'         => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['facebookPhotoAlbums'],
			'inputType' => 'checkbox',
			'exclude' => true,
			'eval'	=> array(
					'multiple' => true
				),
			'sql'	=> 'BLOB NULL',
			'options_callback' => array('FacebookPhotoGalleryEngine', 'loadFacebookPhotoAlbumsForUser'),
			'save_callback'	=> array(array('FacebookPhotoGalleryEngine', 'checkForUpdates'))
		),
		'published'	=> array(
			'label'               => &$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['published'],
    		'exclude'             => true,
    		'filter'              => true,
    		'inputType'           => 'checkbox',
			'eval'      => array(
								'tl_class'    => 'm12',
 							),
    		'sql'                 => "char(1) NOT NULL default ''"
		)
	)
);

class tl_facebook_photo_gallery extends Backend {

	public function addAlbumRequestButton() {
		return '<input id="addAlbumRequestButton" class="tl_submit m12" disabled value="'.$GLOBALS['TL_LANG']['tl_facebook_photo_gallery']['addAlbumRequestButton'].'" type="button" />
				<script type="text/javascript">
					document.getElementById("ctrl_facebookUser").addEventListener("change", function() {
						document.getElementById("addAlbumRequestButton").disabled = false;
					});

				</script>';
	}
	
}