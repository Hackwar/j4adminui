<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Field View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var  \JForm
	 *
	 * @since   3.7.0
	 */
	protected $form;

	/**
	 * @var  \JObject
	 *
	 * @since   3.7.0
	 */
	protected $item;

	/**
	 * @var  \JObject
	 *
	 * @since   3.7.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     HtmlView::loadTemplate()
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		$this->canDo = ContentHelper::getActions($this->state->get('field.component'), 'field', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		Factory::getApplication()->input->set('hidemainmenu', true);

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		$component = $this->state->get('field.component');
		$section   = $this->state->get('field.section');
		$userId    = Factory::getUser()->get('id');
		$canDo     = $this->canDo;

		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load component language file
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_ADMINISTRATOR)
		|| $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

		$title = Text::sprintf('COM_FIELDS_VIEW_FIELD_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE', Text::_(strtoupper($component)));

		// Prepare the toolbar.
		ToolbarHelper::title(
			$title,
			'puzzle field-' . ($isNew ? 'add' : 'edit') . ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-field-' .
			($isNew ? 'add' : 'edit')
		);

		// help button
		ToolbarHelper::help('JHELP_COMPONENTS_FIELDS_FIELDS_EDIT');

		// For new records, check the create permission.
		if ($isNew)
		{
			// cancel button
			ToolbarHelper::cancel('field.cancel');

			// save button's group
			ToolbarHelper::saveGroup(
				[
					['apply', 'field.apply'],
					['save', 'field.save'],
					['save2new', 'field.save2new']
				],
				'btn-success'
			);
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			// cancel button
			ToolbarHelper::cancel('field.cancel', 'JTOOLBAR_CLOSE');

			// save button's group
			$toolbarButtons = [];
			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				$toolbarButtons[] = ['apply', 'field.apply'];
				$toolbarButtons[] = ['save', 'field.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'field.save2new'];
				}
			}

			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'field.save2copy'];
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);
		}
	}
}
