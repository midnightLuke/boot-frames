<?php

namespace BootFrame\Twig;

use Twig\Extension\AbstractExtension;

class UrlExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('url', [$this, 'url'], ['needs_context' => true]),
            new \Twig_Function('route', [$this, 'route'], ['needs_context' => true]),
            new \Twig_Function('route_reverse', [$this, 'routeReverse'], ['needs_context' => true]),
        ];
    }

    /**
     * Helper function to properly prefix absolute URLs and append query strings,
     * preserves specific queries for use in application.
     *
     * @param mixed  $context The twig context.
     * @param string $path    The path to generate a URL for.
     * @param array  $query   The query.
     *
     * @return string The url.
     */
    public function url($context, string $path = null, array $query = []): string
    {
        $request = $context['request'];
        $config = $context['config'];

        // if this is passed as null use current path
        if (is_null($path)) {
            $path = $request->getPathInfo();
        }

        // prefix absolute URLs with basepath
        if (substr($path, 0, 1) == '/') {
            $path = $request->getBasePath() . $path;
        }

        // determine query to use
        if (count($query) > 0 || !empty($_GET)) {
            // preserve these queries over links on the site
            foreach ($config['url_processor']['query']['preserve'] as $preserve) {
                if (!array_key_exists($preserve, $query) && isset($_GET[$preserve])) {
                    $query[$preserve] = $_GET[$preserve];
                }
            }
            $query = '?' . http_build_query($query);
        } else {
            $query = '';
        }

        // cut trailing '?' if that is the last character
        return $path . rtrim($query, '?');
    }

    /**
     * Looks up a route from the available routes and returns a link if
     * available.
     *
     * @param mixed  $context The twig context.
     * @param string $name    The route to generate a URL for.
     * @param array  $query   The query.
     *
     * @return string The url
     */
    public function route($context, string $name, array $query = []): string
    {
        $routes = $context['routes'];
        foreach ($routes as $route_name => $route) {
            if ($route_name == $name) {
                return Url::url($context, $route['path'], $query);
            }
        }
        return '#';
    }

    /**
     * Looks up a url from the available routes and returns a link if available.
     *
     * @param mixed  $context The twig context.
     * @param string $name    The route to generate a URL for.
     * @param array  $query   The query.
     *
     * @return string The url.
     */
    public function routeReverse($context, string $name, array $query = []): string
    {
        $routes = $context['routes'];
        foreach ($routes as $route_name => $route) {
            if ($route['path'] == $name) {
                return Url::url($context, $route['path'], $query);
            }
        }
        return '#';
    }
}
