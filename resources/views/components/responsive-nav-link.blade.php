@props(['active'])

{{ $attributes->class([
    'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium focus:outline-none transition duration-150 ease-in-out',
    'border-neon text-neon bg-neon/10 focus:text-neon focus:bg-neon/10 focus:border-neon' => $active,
    'border-transparent text-creamDim hover:text-cream hover:bg-navy-card/60 hover:border-line/40 focus:text-cream focus:bg-navy-card/60 focus:border-line/40' => ! $active,
]) }}

{{ $slot }}
