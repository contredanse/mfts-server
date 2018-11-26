<?php declare(strict_types=1);

$allow_insecure_http = strtoupper(getenv('JWT_ALLOW_INSECURE_HTTP') ?: '') === 'ON';
$relaxed_hosts = explode(',', getenv('JWT_RELAXED_HTTP_HOSTS') ?: '');

return [
    'token_manager' => [
        'private_key' => getenv('JWT_SIGNING_KEY'),
		'allow_insecure_http' => $allow_insecure_http,
		'relaxed_hosts' => $relaxed_hosts,
		'default_expiry' => (int) (getenv('JWT_TOKEN_DEFAULT_EXPIRY') ?: 3600),
		'default_issuer' => getenv('JWT_TOKEN_DEFAULT_ISSUER'),
		'default_audience' => getenv('JWT_TOKEN_DEFAULT_AUDIENCE'),
    ]
];
