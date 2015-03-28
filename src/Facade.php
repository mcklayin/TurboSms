<?php namespace Newway\TurboSms;



class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'turbo_sms';
    }
}