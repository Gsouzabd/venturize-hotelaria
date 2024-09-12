@props([
    'route',
    'routeParams' => [],
    'class' => '',
    'title',
])

<a href="{{ route($route, $routeParams) }}"
   class="btn btn-secondary rounded-pill d-block ml-md-2 mb-2 mb-md-0{{ $class ? ' ' . $class : '' }}">{{ $title }}</a>
