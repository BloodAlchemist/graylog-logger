<?php

namespace GraylogLogger\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class GraylogLoggerFacade
 *
 * @package GraylogLogger\Laravel
 */
class GraylogLoggerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'GraylogLogger';
    }
}
