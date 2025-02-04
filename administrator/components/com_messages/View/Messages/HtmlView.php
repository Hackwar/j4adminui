<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\View\Messages;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of messages.
 *
 * @since  1.6
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
	 * @var  \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \JObject
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
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
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
		$state = $this->get('State');
		$canDo = ContentHelper::getActions('com_messages');
		ToolbarHelper::title(Text::_('COM_MESSAGES_MANAGER_MESSAGES'), 'envelope inbox');

		$bar = Toolbar::getInstance('toolbar');
		
		if ($canDo->get('core.edit.state'))
		{
			$dropdown = $bar->dropdownButton('status-group')
				->text('JTOOLBAR_SELECT_ACTION')
				->toggleSplit(false)
				->icon('icon-select')
				->buttonClass('btn btn-secondary')
				->listCheck(true);

			$childbar = $dropdown->getChildToolbar();

			$childbar->publish('messages.publish')
				->text('COM_MESSAGES_TOOLBAR_MARK_AS_READ')
				->listCheck(true);
		
			$childbar->unpublish('messages.unpublish')
				->text('COM_MESSAGES_TOOLBAR_MARK_AS_UNREAD')
				->listCheck(true);
			
			
			if ($state->get('filter.state') != -2)
			{
				$childbar->trash('messages.trash')
					->listCheck(true);
			}
			
		}

		$bar->appendButton(
			'Popup',
			'cog',
			'COM_MESSAGES_TOOLBAR_MY_SETTINGS',
			'index.php?option=com_messages&amp;view=config&amp;tmpl=component',
			500,
			250,
			0,
			0,
			'',
			'COM_MESSAGES_TOOLBAR_MY_SETTINGS',
			'<button type="button" class="btn btn-secondary" data-dismiss="modal">'
			. Text::_('JCANCEL')
			. '</button>'
			. '<button type="button" class="btn btn-success" data-dismiss="modal"'
			. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#modal-cog\', buttonSelector: \'#saveBtn\'})">'
			. Text::_('JSAVE')
			. '</button>'
		);

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'messages.delete', 'JTOOLBAR_EMPTY_TRASH');
		}

		
		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_MESSAGING_INBOX');
		
		if ($canDo->get('core.admin'))
		{
			ToolbarHelper::preferences('com_messages');
		}

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('message.add');
		}
	}
}
