<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_draft_article
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;

HTMLHelper::_('script', 'mod_draft_article/draftarticle.min.js', ['version' => 'auto', 'relative' => true]);

require_once __DIR__ . '/helper.php';

$categoryField = ModDraftArticleHelper::getCategories();

// Render the module
require ModuleHelper::getLayoutPath('mod_draft_article', $params->get('layout', 'default'));