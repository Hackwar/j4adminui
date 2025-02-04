<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Plugins list controller class.
 *
 * @since  1.6
 */
class PluginsController extends AdminController
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Plugin', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get the number of activated plugins
	 *
	 * @return  string  The JSON-encoded amount of items
	 *
	 * @since   4.0
	 */
	public function getQuickiconContent()
	{
		try {
			$model = $this->getModel('Plugins');

			$model->setState('filter.enabled', 1);
	
			$amount = (int) $model->getTotal();
	
			$result = [];
	
			$result['amount'] = $this->numberShorten($amount);
			$result['sronly'] = Text::plural('COM_PLUGINS_N_QUICKICON_SRONLY', $amount);
			$result['name'] = Text::plural('COM_PLUGINS_N_QUICKICON', $amount);
	
			echo new JsonResponse($result);
		} catch ( Exception $e) {
			echo new JsonResponse(['success'=>false, 'message'=>$e->getMessage()]);
		}
		
	}
}
