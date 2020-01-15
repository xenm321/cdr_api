<?php

namespace App;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use App\Api\ApiProblemResponseFactory;
use App\Repository\CallsRepository;
use App\Security\Authentication\ApiEntryPoint;
use App\Security\Authentication\ApiTokenListener;
use App\Security\Authentication\ApiTokenProvider;
use App\Services\CallRecordsService;
use App\Services\Mysql;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Silex\Application as SilexApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Application extends SilexApplication
{
    private $defaultControllerDir = 'src/Controller';

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureProviders();
        $this->configureServices();
        $this->configureSecurity();
        $this->configureListeners();
    }

    public function mountControllers()
    {
        $controllerPath = isset($this['controller_dir']) ? $this['controller_dir']: $this->defaultControllerDir;

        $finder = new Finder();
        $finder->in($this['root_dir'].'/'.$controllerPath)
            ->name('*Controller.php')
        ;

        foreach ($finder as $file) {
            $cleanedPathName = $file->getRelativePathname();
            $cleanedPathName = str_replace('/', '\\', $cleanedPathName);
            $cleanedPathName = str_replace('.php', '', $cleanedPathName);

            $class = 'App\\Controller\\'.$cleanedPathName;

            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            $this->mount('/', new $class($this));
        }
    }

    private function configureProviders()
    {
        $app = $this;

        $env = isset($app['env'])? $app['env']: '';

        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => $this['root_dir'].'/var/logs/app.'.$env.'.log',
            'monolog.level' => Logger::ERROR,
            'monolog.handler' => function () use ($app) {
                $level = MonologServiceProvider::translateLevel($app['monolog.level']);

                return new RotatingFileHandler(
                    $app['monolog.logfile'],
                    10,
                    $level,
                    $app['monolog.bubble'],
                    $app['monolog.permission']
                );
            },
        ));
    }

    private function configureServices()
    {
        $app = $this;

        $app['db'] = $app->share(function () {
            return new Mysql(
                getenv('DATABASE_HOST'),
                getenv('DATABASE_USER'),
                getenv('DATABASE_PASSWORD'),
                getenv('DATABASE_NAME')
            );
        });

        $this['api.response_factory'] = $this->share(function() use ($app) {
            return new ApiProblemResponseFactory();
        });

        $this['app.services.call_records'] = $this->share(function() use ($app) {
            return new CallRecordsService(
                $app['db'],
                getenv('FILE_RECORDS_DIR')
            );
        });

        $this['app.repository.calls'] = $this->share(function() use ($app) {
            return new CallsRepository($app['db']);
        });
    }

    private function configureSecurity()
    {
        $app = $this;

        $this->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'main' => array(
                    'pattern' => '^/',
                    'stateless' => true,
                    'anonymous' => true,
                    'http' => true,
                    'api_token' => true,
                ),
            )
        ));

        $this['security.access_rules'] = array(
            array('^/', 'IS_AUTHENTICATED_FULLY'),
        );

        $app['security.authentication_listener.factory.api_token'] = $app->protect(function ($name, $options) use ($app) {
            $app['security.authentication_listener.'.$name.'.api_token'] = $app->share(function () use ($app) {
                return new ApiTokenListener($app['security'], $app['security.authentication_manager']);
            });

            $app['security.authentication_provider.'.$name.'.api_token'] = $app->share(function () use ($app) {
                return new ApiTokenProvider();
            });

            $app['security.entry_point.'.$name.'.api_token'] = $app->share(function() use ($app) {
                return new ApiEntryPoint($app['api.response_factory'], $app['logger']);
            });

            return array(
                'security.authentication_provider.'.$name.'.api_token',
                'security.authentication_listener.'.$name.'.api_token',
                'security.entry_point.'.$name.'.api_token',
                'pre_auth'
            );
        });
    }

    private function configureListeners()
    {
        $app = $this;

        $this->error(function(\Exception $e, $statusCode) use ($app) {
            if($app['debug'] && $statusCode == 500) {
                return;
            }

            if($e instanceof ApiProblemException) {
                $apiProblem = $e->getApiProblem();
            } else {
                $apiProblem = new ApiProblem($statusCode);

                if($e instanceof HttpException || $e instanceof \DomainException) {
                    $apiProblem->set('detail', $e->getMessage());
                }
            }

            return $app['api.response_factory']->createResponse($apiProblem);
        });
    }
}
