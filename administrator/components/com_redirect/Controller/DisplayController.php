<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

/**
 * Redirect master display controller.
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'links';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   mixed    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static	 This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Load the submenu.
		RedirectHelper::addSubmenu($this->input->get('view', 'links'));

		$view   = $this->input->get('view', 'links');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if ($view === 'links')
		{
			$pluginEnabled      = PluginHelper::isEnabled('system', 'redirect');
			$collectUrlsEnabled = RedirectHelper::collectUrlsEnabled();

			// Show messages about the enabled plugin and if the plugin should collect URLs
			if ($pluginEnabled && $collectUrlsEnabled)
			{
				$this->app->enqueueMessage(Text::sprintf('COM_REDIRECT_COLLECT_URLS_ENABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED')), 'notice');
			}
			else
			{
				$redirectPluginId = RedirectHelper::getRedirectPluginId();
				$link = HTMLHelper::_(
					'link',
					'#',
					Text::_('COM_REDIRECT_SYSTEM_PLUGIN'),
					'class="alert-link" data-href="#plugin '. $redirectPluginId .'Modal" data-toggle="modal" id="title-' . $redirectPluginId . '"'
				);

				if ($pluginEnabled && !$collectUrlsEnabled)
				{
					$this->app->enqueueMessage(
						Text::sprintf('COM_REDIRECT_COLLECT_MODAL_URLS_DISABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED'), $link),
						'notice'
					);
				}
				else
				{
					$this->app->enqueueMessage(Text::sprintf('COM_REDIRECT_PLUGIN_MODAL_DISABLED', $link), 'error');
				}
			}
		}

		// Check for edit form.
		if ($view == 'link' && $layout == 'edit' && !$this->checkEditId('com_redirect.edit.link', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_redirect&view=links', false));

			return false;
		}

		parent::display();
	}
}
