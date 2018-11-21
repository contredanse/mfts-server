<?php

declare(strict_types=1);

namespace App\Service\Exception;

class TokenAudienceException extends TokenValidationException
{
	public function getReason(): string
	{
		return 'audience';
	}
}
