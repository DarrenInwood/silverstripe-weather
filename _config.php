<?php

// Adds Weather to all Content Controllers, allowing you to use in templates.
// <% control Weather %>$Title<% end_control %>
// <% control Weather(NZXX0049) %>Title<% end_control %>

Object::add_extension('ContentController', 'WeatherDecorator');

