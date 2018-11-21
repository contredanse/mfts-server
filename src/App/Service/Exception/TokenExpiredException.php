<?php

declare(strict_types=1);

namespace App\Service\Exception;

class TokenExpiredException extends TokenValidationException
{
	public function getReason(): string
	{
		return 'expired';
	}
}
