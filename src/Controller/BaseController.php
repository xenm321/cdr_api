<?php

namespace App\Controller;

use App\Application;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

abstract class BaseController implements ControllerProviderInterface
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    abstract protected function addRoutes(ControllerCollection $controllers);

    public function connect(SilexApplication $app)
    {
        $controllers = $app['controllers_factory'];

        $this->addRoutes($controllers);

        return $controllers;
    }
}
