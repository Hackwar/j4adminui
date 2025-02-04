<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\View\Category;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Categories component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * Flag if an association exists
	 *
	 * @var  boolean
	 */
	protected $assoc;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	/**
	 * Is there a content type associated with this category aias
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $checkTags = false;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$section = $this->state->get('category.section') ? $this->state->get('category.section') . '.' : '';
		$this->canDo = ContentHelper::getActions($this->state->get('category.component'), $section . 'category', $this->item->id);
		$this->assoc = $this->get('Assoc');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Check if we have a content type for this alias
		if (!empty(TagsHelper::getTypes('objectList', array($this->state->get('category.extension') . '.category'), true)))
		{
			$this->checkTags = true;
		}

		Factory::getApplication()->input->set('hidemainmenu', true);

		// If we are forcing a language in modal (used for associations).
		if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
		{
			// Set the language field to the forcedLanguage and disable changing it.
			$this->form->setValue('language', null, $forcedLanguage);
			$this->form->setFieldAttribute('language', 'readonly', 'true');

			// Only allow to select categories with All language or with the forced language.
			$this->form->setFieldAttribute('parent_id', 'language', '*,' . $forcedLanguage);

			// Only allow to select tags with All language or with the forced language.
			$this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
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
		$extension = Factory::getApplication()->input->get('extension');
		$user = Factory::getUser();
		$userId = $user->id;

		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Avoid nonsense situation.
		if ($extension == 'com_categories')
		{
			return;
		}

		// The extension can be in the form com_foo.section
		$parts = explode('.', $extension);
		$component = $parts[0];
		$section = (count($parts) > 1) ? $parts[1] : null;
		$componentParams = ComponentHelper::getParams($component);

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// Get the results for each action.
		$canDo = $this->canDo;

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = $component . ($section ? "_$section" : '') . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE'))
		{
			$title = Text::_($component_title_key);
		}
		// Else if the component section string exits, let's use it
		elseif ($lang->hasKey($component_section_key = $component . ($section ? "_$section" : '')))
		{
			$title = Text::sprintf('COM_CATEGORIES_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT')
				. '_TITLE', $this->escape(Text::_($component_section_key))
			);
		}
		// Else use the base title
		else
		{
			$title = Text::_('COM_CATEGORIES_CATEGORY_BASE_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE');
		}

		// Load specific css component
		HTMLHelper::_('stylesheet', $component . '/administrator/categories.css', array('version' => 'auto', 'relative' => true));

		// Prepare the toolbar.
		ToolbarHelper::title(
			$title,
			'folder category-' . ($isNew ? 'add' : 'edit')
				. ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-category-' . ($isNew ? 'add' : 'edit')
		);
		// Compute the ref_key
		$ref_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT') . '_HELP_KEY';

		// Check if thr computed ref_key does exist in the component
		if (!$lang->hasKey($ref_key))
		{
			$ref_key = 'JHELP_COMPONENTS_'
						. strtoupper(substr($component, 4) . ($section ? "_$section" : ''))
						. '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT');
		}

		/*
		 * Get help for the category/section view for the component by
		 * -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		 * -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		 * -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		 */
		if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL'))
		{
			$debug = $lang->setDebug(false);
			$url = Text::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories($component, 'core.create')) > 0))
		{
			// help button
			ToolbarHelper::divider();
			ToolbarHelper::help($ref_key, $componentParams->exists('helpURL'), $url, $component);
			// cancel button
			ToolbarHelper::cancel('category.cancel');
			// save buttons
			ToolbarHelper::saveGroup(
				[
					['apply', 'category.apply'],
					['save', 'category.save'],
					['save2new', 'category.save2new']
				],
				'btn-success'
			);
		}

		// If not checked out, can save the item.
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId);

			// save button
			if (ComponentHelper::isEnabled('com_contenthistory') && $componentParams->get('save_history', 0) && $itemEditable)
			{
				$typeAlias = $extension . '.category';
				ToolbarHelper::versions($typeAlias, $this->item->id);
			}
			// language associations
			if (Associations::isEnabled() && ComponentHelper::isEnabled('com_associations'))
			{
				ToolbarHelper::custom('category.editAssociations', 'multilingual', 'multilingual', 'JTOOLBAR_ASSOCIATIONS', false, false);
			}
			// help button
			ToolbarHelper::divider();
			ToolbarHelper::help($ref_key, $componentParams->exists('helpURL'), $url, $component);
			// cancel button
			ToolbarHelper::cancel('category.cancel', 'JTOOLBAR_CLOSE');
			// save toolbar buttons
			$toolbarButtons = [];
			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				$toolbarButtons[] = ['apply', 'category.apply'];
				$toolbarButtons[] = ['save', 'category.save'];
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'category.save2new'];
				}
			}
			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'category.save2copy'];
			}
			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);
		}
	}
}
