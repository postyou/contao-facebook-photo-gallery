<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'FacebookPhotoGallery',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'FacebookPhotoGallery\FacebookPhotoGalleryEngine'  => 'system/modules/facebook-photo-gallery/classes/FacebookPhotoGalleryEngine.php',

	// Elements
	'FacebookPhotoGallery\ContentFacebookPhotoGallery' => 'system/modules/facebook-photo-gallery/elements/ContentFacebookPhotoGallery.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'facebook_photo_gallery_default'  => 'system/modules/facebook-photo-gallery/templates',
	'ce_facebook_photo_gallery'       => 'system/modules/facebook-photo-gallery/templates',
	'facebook_photo_gallery_overview' => 'system/modules/facebook-photo-gallery/templates',
));
