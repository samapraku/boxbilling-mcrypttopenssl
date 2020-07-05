<?php
/**
 * BoxBilling
 *
 * @copyright BoxBilling, Inc (http://www.boxbilling.com)
 * @license   Apache-2.0
 *
 * Copyright BoxBilling, Inc
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */


require_once dirname(dirname(__FILE__)) . '/bb-load.php';
$di = include dirname(dirname(__FILE__)) . '/bb-di.php';
include dirname(__FILE__) . '/McryptOpenSSL.php';
include dirname(__FILE__) . '/CryptOpenSSL.php';
$di['translate']();

$service = new McryptOpenSSL($di);
$service->convert_email_template_to_openssl();
$service->extension_config_to_openssl();

unset($service, $di);