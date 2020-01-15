<?php

namespace App\Controller;

use Silex\ControllerCollection;

class DefaultController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/', array($this, 'homepage'));
    }

    public function homepage()
    {
        return $this->app->json(array('version' => '1.0.0'));
    }
}
