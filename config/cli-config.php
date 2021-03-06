<?php
/**
 * For doctrine integration
 */

// Load dotenv

require __DIR__ . '/init-env.php';

$container = require 'container.php';

return new \Symfony\Component\Console\Helper\HelperSet([
	'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper(
		$container->get('doctrine.entity_manager.orm_default')
	),
]);
