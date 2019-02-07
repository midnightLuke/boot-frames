<?php

namespace BootFrame;

use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use BootFrame\Twig\UrlExtension;

/**
 * Main application for BootFrame, can be used as follows:
 *
 * define('BASE_DIR', __DIR__ . '/..');
 * define('WEB_DIR', __DIR__);
 *
 * use BootFrame\App;
 *
 * $app = new App;
 * $app->run();
 */
class App
{
    protected $request;
    protected $config;
    protected $routes;

    /**
     * Main entry point for the BootFrames app, will construct request, routes
     * and twig environment then run.
     *
     * @return void
     */
    public function run(): void
    {
        // Parse the config.
        $this->config = Yaml::parse(file_get_contents(BASE_DIR . '/config/config.yml'));

        // Parse the request.
        $this->request = Http\Request::createFromGlobals();

        // Do authorization.
        $this->doAuth();

        // Generate routing.
        if ($this->config['routing_type'] == 'scan') {
            $this->routes = $this->templateScan();
        } elseif ($this->config['routing_type'] == 'yaml') {
            $this->routes = Yaml::parse(file_get_contents(BASE_DIR . '/config/routes.yml'));
        }

        // Setup the twig environment.
        $loader = new \Twig_Loader_Filesystem(BASE_DIR . '/templates');
        $twig = new \Twig_Environment($loader, $this->config['twig']);

        // Add some useful globals.
        $twig->addGlobal('request', $this->request);
        $twig->addGlobal('config', $this->config);
        $twig->addGlobal('routes', $this->routes);
        $twig->addGlobal('authenticated', (isset($_GET['authenticated'])));
        $twig->addGlobal('privileged', (isset($_GET['privileged'])));
        $twig->addGlobal('GET', $_GET);

        // Add our custom twig extensions.
        $twig->addExtension(new UrlExtension());

        // Path to match for routing.
        $path = $this->request->getPathInfo();
        $route = $this->matchRoute($path, $this->routes);

        // Render a twig template.
        if ($route !== null) {
            $response = new Http\Response($twig->render($route['template']));
        } else {
            // Render index.
            if ($this->request->getPathInfo() == '/') {
                $response = new Http\Response($twig->render('index.html.twig'));

            // 404.
            } else {
                $response = new Http\Response($twig->render('404.html.twig'), Http\Response::HTTP_NOT_FOUND);
            }
        }

        // Send the response.
        $response->send();
    }

    /**
     * Scans directory structure for wireframes using configuration from
     * config.yml. Uses the `path` component to fill in various missing details
     * for display purposes.
     *
     * @return array A structured array of routes.
     */
    private function templateScan(): array
    {
        // Store routes here.
        $routes = [];

        // Get a finder.
        $files = new Finder();
        $files->files()
        ->in(BASE_DIR . '/templates');

        // Configure finder.
        foreach ($this->config['scan_options']['exclude_dir'] as $dir) {
            $files->exclude($dir);
        }
        foreach ($this->config['scan_options']['include_name'] as $name) {
            $files->name($name);
        }
        foreach ($this->config['scan_options']['exclude_name'] as $name) {
            $files->notName($name);
        }

        // Create routes from files.
        foreach ($files as $file) {
            $path = '/' . str_replace('.html.twig', '', $file->getRelativePathname());
            $routes[$path] = [
                'path' => $path,
                'template' => $file->getRelativePathname(),
                'description' => $path,
            ];
        }

        return $routes;
    }

    /**
     * Matches a route based on the path passed in.
     *
     * @param  string $path   The path to search for in the passed routes array.
     * @param  array  $routes A structured array of routes to search through.
     * @return ?array         The route within route array.
     */
    private function matchRoute(string $path, array $routes): ?array
    {
        foreach ($routes as $route) {
            if ($path == $route['path']) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Function that performs simple basic auth.  If the user is not found in the
     * configured array this returns a 401 response and terminates the script.
     *
     * @return void
     */
    private function doAuth(): void
    {
        // Only BASIC auth is configured.
        if ($this->config['auth']['type'] != 'basic') {
            return;
        }

        // Get users from configuration.
        $users = $this->config['auth']['users'];

        // Start with null.
        $username = $password = '';

        // mod_php handling.
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];

        // Most other servers.
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic')===0) {
                list($username,$password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            }
        } elseif (isset($_SERVER['Authorization'])) {
            if (strpos(strtolower($_SERVER['Authorization']), 'basic')===0) {
                list($username,$password) = explode(':', base64_decode(substr($_SERVER['Authorization'], 6)));
            }
        }

        // Continue to send 401 until user is authorized correctly.
        if (!isset($users[$username]) || $users[$username] != $password) {
            $response = new Http\Response('401 - Not authorized', Http\Response::HTTP_UNAUTHORIZED, [
                'WWW-Authenticate' => 'Basic realm="Boot Frames"',
            ]);
            $response->send();
            exit();
        }
    }
}
