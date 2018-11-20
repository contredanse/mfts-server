<?php declare(strict_types=1);

return [
    'token_manager' => [
        'privateKey' => getenv('JWT_SIGNING_KEY')
    ]
];