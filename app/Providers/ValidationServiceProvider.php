<?php
namespace App\Providers;

use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('conditionType', 'App\Validators\TableValidator@conditionType');
        Validator::extend('conditionsCount', 'App\Validators\TableValidator@conditionsCount');
        Validator::extend('conditionsFieldKey', 'App\Validators\TableValidator@conditionsFieldKey');
        Validator::extend('ruleThanType', 'App\Validators\TableValidator@ruleThanType');
        Validator::extend('probabilitySum', 'App\Validators\TableValidator@probabilitySum');
        Validator::extend('mongoId', function ($attribute, $value) {
            return preg_match('/^(?=[a-f\d]{24}$)(\d+[a-f]|[a-f]+\d)/', $value);
        });
        Validator::extend('metaKeysAmount', function ($attribute, $value) {
            return $value <= 24;
        });
        Validator::extend('betweenString', function ($attribute, $value) {
            if (strpos($value, ';') === false) {
                return false;
            }

            $between = array_map(function ($item) {
                return floatval(str_replace(',', '.', $item));
            }, explode(';', $value));

            if (count($between) > 2) {
                return false;
            }
            if (!is_numeric($between[0]) or !is_numeric($between[1])) {
                return false;
            }

            return $between[0] < $between[1];
        });
        Validator::extend('password', 'App\Validators\UserValidator@password');
        Validator::extend('username', 'App\Validators\UserValidator@username');
        Validator::extend('last_name', 'App\Validators\UserValidator@lastName');
    }

    public function register()
    {
        $this->app->singleton('validation.presence', function ($app) {
            return new DatabasePresenceVerifier($app['db']);
        });

        $this->app->singleton('validator', function ($app) {
            $validator = new Factory($app['translator'], $app);
            $validator->resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
                return new \App\Http\Services\Validator($translator, $data, $rules, $messages, $customAttributes);
            });

            // The validation presence verifier is responsible for determining the existence
            // of values in a given data collection, typically a relational database or
            // other persistent data stores. And it is used to check for uniqueness.
            if (isset($app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            return $validator;
        });
    }
}
