@props(['active'])

{{ $attributes->class([
    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out',
    'border-neon text-cream focus:border-neon' => $active,
    'border-transparent text-creamDim hover:text-cream hover:border-line/40 focus:text-cream focus:border-line/40' => ! $active,
]) }}

{{ $slot }}
