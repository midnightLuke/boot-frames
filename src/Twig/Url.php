<?php

namespace BootFrame\Twig;

class Url {
  /**
   * Helper function to properly prefix absolute URLs and append query strings,
   * preserves specific queries for use in application.
   */
  public static function url($context, $path = null, $query = array()) {
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
   * Looks up a route from the available routes and returns a link if available.
   */
  public static function route($context, $name, $query = array()) {
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
   */
  public static function route_reverse($context, $name, $query = array()) {
    $routes = $context['routes'];
    foreach ($routes as $route_name => $route) {
      if ($route['path'] == $name) {
        return Url::url($context, $route['path'], $query);
      }
    }
    return '#';
  }
}

