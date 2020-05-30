<?php

namespace WebDevEtc\BlogEtc\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class BlogEtcAuthGateNotImplementedException extends ModelNotFoundException
{
    public function __construct($_ = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('You must implement your own gate in AuthServiceProvider for the \WebDevEtc\BlogEtc\Gates\GateTypes::MANAGE_ADMIN gate.', $code, $previous);

        // Add something like the following to AuthServiceProvider:

        //  Gate::define(GateTypes::MANAGE_ADMIN, static function (?Model $user) {
        //      Implement your logic to allow or disallow admin access for $user
        //      return $model->is_admin === true;
        //      or:
        //      return $model->email === 'your-email@your-site.com';
        //  });
    }
}
