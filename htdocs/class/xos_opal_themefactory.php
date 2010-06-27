<?php
/**
 * xos_opal_ThemeFactory
 *
 * Gateway file to include class/theme.php when xos_opal_ThemeFactory class is needed
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id: icmsmetagen.php 19118 2010-03-27 17:46:23Z skenow $
 * @deprecated	This will be removed in version 1.4
 */

if (!defined("ICMS_ROOT_PATH")) die("ImpressCMS root path not defined");

include_once(ICMS_ROOT_PATH . '/class/theme.php');
?>