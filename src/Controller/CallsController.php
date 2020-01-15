<?php

namespace App\Controller;

use App\Repository\CallsRepository;
use App\Services\CallRecordsService;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class CallsController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->get('/calls', array($this, 'calls'));
        $controllers->get('/calls/{uniqueId}/record-file/', array($this, 'recordFile'));
    }

    public function calls(Request $request)
    {
        $filters = $request->query->all();

        /**
         * @var CallsRepository $repository
         */
        $repository = $this->app['app.repository.calls'];

        return $this->app->json($repository->findAll($filters));
    }

    public function recordFile($uniqueId)
    {
        /**
         * @var CallRecordsService $service
         */
        $service = $this->app['app.services.call_records'];

        $file = $service->getFile($uniqueId);

        return new BinaryFileResponse($file);
    }
}
