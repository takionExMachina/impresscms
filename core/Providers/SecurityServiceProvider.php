<?php

namespace ImpressCMS\Core\Providers;

use ImpressCMS\Core\Event;
use ImpressCMS\Core\Security\RequestSecurity;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Security service provider
 */
class SecurityServiceProvider extends AbstractServiceProvider
{

	/**
	 * @inheritdoc
	 */
	protected $provides = [
		'security'
	];

	/**
	 * @inheritdoc
	 */
	public function register()
	{
		$this->getContainer()->add('security', function () {
			$instance = new RequestSecurity();
			$instance->checkSuperglobals();
			if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {
				define('XOOPS_DB_PROXY', 1);
			}
			Event::attach('icms', 'loadService-config', array($instance, 'checkBadips'));
			return $instance;
		});
	}
}