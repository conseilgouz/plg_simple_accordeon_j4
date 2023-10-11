<?php
/**
 * @version		2.1.2
 * @package		Simple Accordeon content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2023 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v3; see LICENSE.php
 **/
defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use ConseilGouz\Plugin\Content\Simpleaccordeon\Extension\Simpleaccordeon;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
				$plugin     = new Simpleaccordeon(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('content', 'simpleaccordeon')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
