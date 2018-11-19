<?php declare(strict_types=1);

namespace App\Util;

class SubsFormatConvert
{
    protected $menuConvert;

    function __construct(MenuConvert $menuConvert)
    {
        $this->menuConvert = $menuConvert;
    }
}
