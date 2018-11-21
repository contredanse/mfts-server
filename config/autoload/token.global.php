<?php declare(strict_types=1);

$allow_insecure_http = strtoupper(getenv('JWT_ALLOW_INSECURE_HTTP') ?: '') === 'ON';
$relaxed_hosts = explode(',', getenv('JWT_RELAXED_HTTP_HOSTS') ?: '');

return [
    'token_manager' => [
        'privateKey' => getenv('JWT_SIGNING_KEY'),
		'allow_insecure_http' => $allow_insecure_http,
		'relaxed_hosts' => $relaxed_hosts,
    ]
];
