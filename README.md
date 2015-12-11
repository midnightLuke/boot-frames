# Boot Frames

Boot Frames is a wireframing framework that uses [Twig](http://twig.sensiolabs.org/)
and [Bootstrap](http://getbootstrap.com/) to provide a way for web developers to
quickly wireframe and prototype websites.

## Why does this exist?

I am a back-end developer who uses PHP every day to build websites.  I will
occasionally find myself needing to wireframe something to explain a concept to
clients/designers/project managers/etc, or, in some cases, wireframe an entire
section of a site to get a better grasp on a concept myself.  Being a backend
developer I found myself balking at tools like "My Balsamiq" and "Moqups" (which
are great tools for non-technical folks putting together wireframes) and longing
to just be able to write some HTML and not repeat myself so much.

With the advent of some truly amazing templating languages and my own experience
being in Drupal and Symfony I decided to create a simple framework that would
allow me to put together some common elements for a website and quickly put
together wireframes using HTML, CSS and Javascript, the result is Boot Frames.

Boot Frames uses Twig as it's templating engine and Bootstrap as it's base theme
to enable developers to quickly build wireframes in a way that is comfortable
and familiar (and most of the time a lot closer to what the end result will be).
It also provides a set of common tools to make the wireframes easy to navigate
and manipulate for project managers and clients.

## Installation

Either clone this repository or download and unzip a release from the "releases"
tab above.  From the root directory run:

```
composer install
```

to install the dependencies for Boot Frames.

To run bootframes you need to make the `web/` directory available in a web
browser, which can be done in a number of ways on your local, but I'm not going
into those in this documentation.  Visit Boot Frames in the browser and you
should be good to go.

## Configuration

Boot Frames has two files in the `config/` directory:

- `config.yml`: this file contains the global application configuration
- `routes.yml`: this files contains the manually configured routes (this is
optional)

## Creating wireframes

By default Boot Frames will scan the `templates/` for wireframes and
automatically create routes for any templates it finds (this can be overridden
in the config.yml file).

To create a new wireframe just create a new template in the `templates/`
directory (or any sub-directory) and it will automatically appear on the
wireframes list.

For help using Twig check out the excellent
[documentation](http://twig.sensiolabs.org/doc/templates.html) on their website.

### Boot Frame link helpers

Boot Frames comes with several Twig plugins to ease creating links between
wireframes and linking to assets within the `web/` directory.

#### `url`

Use `{{ url('/absolute/url') }}` in your templates to link to an absolute URL in
the web directory, this will prefix the link with any subdirectories required for
the link to function (and the functions below all pass through this function).

#### `route`

Use `{{ route('/route/machine/name') }}` (or `{{ route('route_machine_name') }}`
if using the routes.yml file).  To link to any route you have defined.  If
Boot Frames cannot locate the route it simply outputs a `#` so that you can
create the wireframe later (if you are so inclined) and the link will
automatically activate.

#### `route_reverse`

This functions the same as the `route` function, but instead allows you to input
a path and get the same result (either an active link or a `#` if that path
isn't defined for a route).  This is more useful when using the routes.yml file.

### Boot Frame authentication states

By default Boot Frames sets and maintains two GET parameters "authenticated" and
"privileged", these are set as global twig variables so all templates have
access to them and can modify behaviour accordingly, for example:

```twig
{% if authenticated %}
  Only authenticated users can see this.
{% else %}
  You are not authenticated.
{% endif %}
```

## Navigating wireframes

Boot Frames comes with several helpers that allow users to clearly view all
active links for a wireframe and view the same wireframe as "authenticated" and
"privileged" users.  Controls for toggling "authenticated" and "privileged"
appear in the bottom left corner of the screen, along with a link back to the
wireframe index.

Pressing `q` on any page will highlight all available links using bootstrap
tooltips.  By default the tooltip shows the href attribute of the link, but you
can specify a title to override this.
