Fun experiment to build the most barebones possible component based templating engine in PHP.

Takes advantage of PHP's named arguments to mimic html properties and does a lot of output buffer trickery.

Allows to work with slots as well.

`engine.php` contains all the rendering code. Everything else is an example where `index.php` is an html page you can render
and that utilizes the components `comp.*.php`
