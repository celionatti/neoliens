<?php

declare(strict_types=1);

use Slim\App;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use function DI\create;
use Neoliens\Core\Csrf;
use Clockwork\Clockwork;
use Neoliens\Core\Config;
use Doctrine\ORM\ORMSetup;
use Neoliens\Core\Session;
use Slim\Factory\AppFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use League\Flysystem\Filesystem;
use Neoliens\Core\Enum\SameSite;
use Clockwork\Storage\FileStorage;
use Twig\Extra\Intl\IntlExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Mailer\Mailer;
use Neoliens\Core\Enum\StorageDriver;
use Neoliens\Core\Filters\UserFilter;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Neoliens\Core\Enum\AppEnvironment;
use Symfony\Component\Mailer\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Clockwork\DataSource\DoctrineDataSource;
use Neoliens\Core\DataObjects\SessionConfig;
use Neoliens\Core\Contracts\SessionInterface;
use Neoliens\Core\RouteEntityBindingStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Neoliens\Core\Services\EntityManagerService;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\WebpackEncoreBundle\Asset\TagRenderer;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Neoliens\Core\Contracts\EntityManagerServiceInterface;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;

return [
    App::class                              => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        $addMiddlewares = require CONFIG_PATH . '/middleware.php';
        $router         = require ROUTE_PATH . '/web.php';

        $app = AppFactory::create();

        $app->getRouteCollector()->setDefaultInvocationStrategy(
            new RouteEntityBindingStrategy(
                $container->get(EntityManagerServiceInterface::class),
                $app->getResponseFactory()
            )
        );

        $router($app);

        $addMiddlewares($app);

        return $app;
    },
    Config::class                           => create(Config::class)->constructor(
        require CONFIG_PATH . '/app.php'
    ),
    EntityManagerInterface::class           => function (Config $config) {
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            $config->get('doctrine.entity_dir'),
            $config->get('doctrine.dev_mode')
        );

        $ormConfig->addFilter('user', UserFilter::class);

        return new EntityManager(
            DriverManager::getConnection($config->get('doctrine.connection'), $ormConfig),
            $ormConfig
        );
    },
    Twig::class                             => function (Config $config, ContainerInterface $container) {
        $twig = Twig::create(VIEW_PATH, [
            'cache'       => false,
            // 'cache'       => STORAGE_PATH . '/cache/templates',
            'auto_reload' => AppEnvironment::isDevelopment($config->get('app_environment')),
        ]);

        $twig->addExtension(new IntlExtension());
        $twig->addExtension(new EntryFilesTwigExtension($container));
        $twig->addExtension(new AssetExtension($container->get('webpack_encore.packages')));

        return $twig;
    },
    /**
     * The following two bindings are needed for EntryFilesTwigExtension & AssetExtension to work for Twig
     */
    'webpack_encore.packages'               => fn () => new Packages(
        new Package(new JsonManifestVersionStrategy(BUILD_PATH . '/manifest.json'))
    ),
    'webpack_encore.tag_renderer'           => fn(ContainerInterface $container) => new TagRenderer(
        new EntrypointLookup(BUILD_PATH . '/entrypoints.json'),
        $container->get('webpack_encore.packages')
    ),
    ResponseFactoryInterface::class         => fn (App $app) => $app->getResponseFactory(),
    AuthInterface::class                    => fn (ContainerInterface $container) => $container->get(
        Auth::class
    ),
    UserProviderServiceInterface::class     => fn (ContainerInterface $container) => $container->get(
        UserProviderService::class
    ),
    SessionInterface::class                 => fn (Config $config) => new Session(
        new SessionConfig(
            $config->get('session.name', ''),
            $config->get('session.flash_name', 'flash'),
            $config->get('session.secure', true),
            $config->get('session.httponly', true),
            SameSite::from($config->get('session.samesite', 'lax'))
        )
    ),
    RequestValidatorFactoryInterface::class => fn (ContainerInterface $container) => $container->get(
        RequestValidatorFactory::class
    ),
    'csrf'                                  => fn (ResponseFactoryInterface $responseFactory, Csrf $csrf) => new Guard(
        $responseFactory,
        failureHandler: $csrf->failureHandler(),
        persistentTokenMode: true
    ),
    Filesystem::class                       => function (Config $config) {
        $adapter = match ($config->get('storage.driver')) {
            StorageDriver::Local => new LocalFilesystemAdapter(STORAGE_PATH),
        };

        return new League\Flysystem\Filesystem($adapter);
    },
    Clockwork::class                        => function (EntityManagerInterface $entityManager) {
        $clockwork = new Clockwork();

        $clockwork->storage(new FileStorage(STORAGE_PATH . '/clockwork'));
        $clockwork->addDataSource(new DoctrineDataSource($entityManager));

        return $clockwork;
    },
    EntityManagerServiceInterface::class    => fn(EntityManagerInterface $entityManager) => new EntityManagerService(
        $entityManager
    ),
    MailerInterface::class                  => function (Config $config) {
        $transport = Transport::fromDsn($config->get('mailer.dsn'));

        return new Mailer($transport);
    },
    BodyRendererInterface::class            => fn (Twig $twig) => new BodyRenderer($twig->getEnvironment()),
    RouteParserInterface::class             => fn (App $app) => $app->getRouteCollector()->getRouteParser(),
];
