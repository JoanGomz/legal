{{-- search-input.blade.php --}}
@props([
'mode' => 'default',
'action' => '',
])

@php
switch ($mode) {
    case 'tableSearch':
        $attributesToAdd['wire:model.live.debounce.500ms'] = 'search';
        break;
    
    case 'cardSearch':
        $attributesToAdd['id'] = 'searchInput';
        break;}
@endphp

<div class="relative">
    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg class="w-4 h-4 text-white" aria-hidden="true"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
        </svg>
    </div>
    <input
        type="text"
        {{ $attributes->merge($attributesToAdd)->class("w-full placeholder:text-white pt-2 ps-10 text-sm text-white border shadow-inner shadow-gray-600 border-gray-300 rounded-lg lg:w-80 bg-gray-400 focus:ring-brand-purple focus:border-brand-purple") }}>
    {{ $slot }}
    </input>
</div>