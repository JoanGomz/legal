<?php

use Livewire\Volt\Component;
use app\Models\User;
new class extends Component {
    public function with(): array
    {
        return [
            'users' => User::select('id', 'name', 'email')->toBase()->get(),
        ];
    }
}; ?>

@component('livewire.panels.identity-management.accesos-layout')
    <div class="space-y-4 pb-4">
        <div class="flex justify-between items-center">
            <input type="text" placeholder="Buscar usuario" class="text-white placeholder:text-slate-300 bg-slate-700 shadow-md border-none rounded-lg px-4 py-2 w-full max-w-sm ">
            <button
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-opacity-90 transition-all">
                <i class="fa-solid fa-user-plus mr-2"></i> Nuevo Usuario
            </button>
        </div>
        @foreach ($users as $user)
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-bold text-gray-800">{{ $user->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                    <button class="text-brand-purple hover:text-brand-purple-dark">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endcomponent
