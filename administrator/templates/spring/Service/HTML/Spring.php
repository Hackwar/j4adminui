<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Spring
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use OzdemirBurak\Iris\Color\Hex;
use OzdemirBurak\Iris\Color\Hsl;
use OzdemirBurak\Iris\Color\Rgb;
use OzdemirBurak\Iris\Color\Hsla;

/**
 * Template Spring HTML Helper
 *
 * @since  4.0.0
 */
class JHtmlSpring
{
	/**
	 * Calculates the different template colors and set the CSS variables
	 *
	 * @param   Registry   $params    Template parameters.
	 *
	 * @return void
	 *
	 * @since  4.0.0
	 */
	public static function rootcolors(Registry $params): void
	{
		$root       = [];

		if ($params->get('hue'))
		{
			$root = static::bgdarkcalc($params->get('hue'));
		}

		$bgLight = $params->get('bg-light');

		if (static::isHex($bgLight))
		{
			try
			{
				$bgLight = new Hex($bgLight);

				$root[] = '--spring-bg-light: ' . $bgLight . ';';
				$root[] = '--toolbar-bg: ' . $bgLight->lighten(5) . ';';
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$textDark = $params->get('text-dark');

		if (static::isHex($textDark))
		{
			try
			{
				$textDark = new Hex($textDark);
				$root[]   = '--spring-text-dark: ' . $textDark . ';';
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$textLight = $params->get('text-light');

		if (static::isHex($textLight))
		{
			try
			{
				$textLight = new Hex($textLight);
				$root[]    = '--spring-text-light: ' . $textLight . ';';
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$linkColor = $params->get('link-color');

		if (static::isHex($linkColor))
		{
			try
			{
				$linkColor = new Hex($linkColor);

				$root[] = '--spring-link-color: ' . $linkColor . ';';

				$root[] = '--spring-link-hover-color: ' . $linkColor->darken(20) . ';';
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$specialColor = $params->get('special-color');

		if (static::isHex($specialColor))
		{
			try
			{
				$specialcolor = new Hex($specialColor);
				$root[]       = '--spring-special-color: ' . $specialcolor . ';';
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		if (count($root))
		{
			Factory::getDocument()->addStyleDeclaration(':root {' . implode($root) . '}');
		}
	}

	/**
	 * Calculates the different template colors
	 *
	 * @param   string   $color         Template parameter color.
	 * @param   boolean  $monochrome    Template parameter monochrome.
	 *
	 * @return  array  An array of calculated color values and css variables
	 *
	 * @since  4.0.0
	 */
	protected static function bgdarkcalc($color): array
	{
		$hue = 207;

		if (strpos($color, 'hsl(') !== false)
		{
			try
			{
				$hue = (new Hsl($color))->hue();
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (static::isHex($color))
		{
			try
			{
				$hue = (new Hex($color))->toHsl()->hue();
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (strpos($color, 'hsla(') !== false)
		{
			try
			{
				$hue = (new Hsla($color))->toHsl()->hue();
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}
		elseif (strpos($color, 'rgb(') !== false)
		{
			try
			{
				$hue = (new Rgb($color))->toHsl()->hue();
			}
			catch (\Exception $ex)
			{
				// Just ignore exceptions
			}
		}

		$hue  = min(359, max(0, (int) $hue));
		$root = [];

		// No need to calculate if we have the default value
		if ($hue === 207)
		{
			return $root;
		}

		try
		{
			$bgcolor = new Hsl('hsl(' . $hue . ', ' . 61 . ', 26)');

			$root[] = '--spring-bg-dark: ' . (new Hsl('hsl(' . $hue . ', 61, 26)'))->toHex() . ';';
			$root[] = '--spring-contrast: ' . (new Hsl('hsl(' . $hue . ', 61, 26)'))->spin(-40)->lighten(18)->toHex() . ';';
			$root[] = '--spring-bg-dark-0: ' . (clone $bgcolor)->desaturate(86)->lighten(71.4)->spin(-6)->toHex() . ';';
			$root[] = '--spring-bg-dark-5: ' . (clone $bgcolor)->desaturate(85)->lighten(65.1)->spin(-6)->toHex() . ';';
			$root[] = '--spring-bg-dark-10: ' . (clone $bgcolor)->desaturate(80)->lighten(59.4)->spin(-6)->toHex() . ';';
			$root[] = '--spring-bg-dark-20: ' . (clone $bgcolor)->desaturate(75)->lighten(47.3)->spin(-6)->toHex() . ';';
			$root[] = '--spring-bg-dark-30: ' . (clone $bgcolor)->desaturate(55)->lighten(34.3)->spin(-5)->toHex() . ';';
			$root[] = '--spring-bg-dark-40: ' . (clone $bgcolor)->desaturate(40)->lighten(21.4)->spin(-3)->toHex() . ';';
			$root[] = '--spring-bg-dark-50: ' . (clone $bgcolor)->desaturate(15)->lighten(10)->spin(-1)->toHex() . ';';
			$root[] = '--spring-bg-dark-70: ' . (clone $bgcolor)->lighten(-6)->spin(4)->toHex() . ';';
			$root[] = '--spring-bg-dark-80: ' . (clone $bgcolor)->lighten(-11.5)->spin(7)->toHex() . ';';
			$root[] = '--spring-bg-dark-90: ' . (clone $bgcolor)->desaturate(-1)->lighten(-17)->spin(10)->toHex() . ';';
		}
		catch (\Exception $ex)
		{
			// Just ignore exceptions
		}

		return $root;
	}

	/**
	 * Determinates if the given string is a color hex value
	 *
	 * @param   string  $hex  The string to test
	 *
	 * @return boolean  True when color hex value otherwise false
	 *
	 * @since  4.0.0
	 */
	protected static function isHex($hex): bool
	{
		if (substr($hex, 0, 1) !== '#')
		{
			return false;
		}

		$value = ltrim($hex, '#');

		return (strlen($value) == 6 || strlen($value) == 3) && ctype_xdigit($value);
	}
}
