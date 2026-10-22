<?php

use Livewire\Component;

use Livewire\Attributes\Layout;

new #[Layout('layouts.sidebar-asesor')] class extends Component {
    public function render()
    {
        return view('livewire.panels.asesor.conversation_hub');
    }
};
?>

<div class="flex" style="height: calc(100vh - 65px);">
    @livewire('panels.asesor.conversation_list')
    @livewire('panels.asesor.chat-panel')
    {{--@livewire('panels.asesor.contact_info')--}}
</div>