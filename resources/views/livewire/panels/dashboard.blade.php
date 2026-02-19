<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="py-2">
    <div class=" mx-auto px-2  space-y-6">
        <div class="p-4 bg-white shadow sm:rounded-lg">
            <h1 class="text-xl font-semibold text-gray-900">
                Bienvenid@ <a href="{{ route('profile') }}"
                    class="text-[#0078B6]">{{ auth()->user()->name }}</a> al área legal de StarPark
            </h1>
        </div>
    </div>
</div>
