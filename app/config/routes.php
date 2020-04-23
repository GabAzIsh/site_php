<?php

return array(
    // [0] regular expression for URI , [1] name of controller, [2] slug
    ['^/$','Main', 'main'],
    ['^/(\d){1,6}$', 'Book', 'description'],
    ['^/create$', 'Create', 'create']
);
?>