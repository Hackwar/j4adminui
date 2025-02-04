<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\View\Plugins;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of plugins.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_plugins');

		ToolbarHelper::title(Text::_('COM_PLUGINS_MANAGER_PLUGINS'), 'plugins plugin');

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		// action button
		$dropdown = $toolbar->dropdownButton('status-group')
			->text('JTOOLBAR_SELECT_ACTION')
			->toggleSplit(false)
			->icon('icon-select')
			->buttonClass('btn btn-white')
			->listCheck(true);

		$childBar = $dropdown->getChildToolbar();

		if ($canDo->get('core.edit.state'))
		{
			$childBar->publish('plugins.publish', 'JTOOLBAR_ENABLE')->listCheck(true);
			$childBar->unpublish('plugins.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true);
			$childBar->checkin('plugins.checkin');
		}

		// help button
		$toolbar->help('JHELP_EXTENSIONS_PLUGIN_MANAGER');

		// option button
		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_plugins');
		}

	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value.
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'enabled'      => Text::_('JSTATUS'),
			'name'         => Text::_('JGLOBAL_TITLE'),
			'folder'       => Text::_('COM_PLUGINS_FOLDER_HEADING'),
			'element'      => Text::_('COM_PLUGINS_ELEMENT_HEADING'),
			'access'       => Text::_('JGRID_HEADING_ACCESS'),
			'extension_id' => Text::_('JGRID_HEADING_ID'),
		);
	}
}
