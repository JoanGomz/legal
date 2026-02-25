<div 
    x-data="{ 
        showLoading: false,
        message: 'Cargando...',
        show(msg = null) {
            if (msg) this.message = msg;
            this.showLoading = true;
        },
        hide() {
            this.showLoading = false;
        }
    }" 
    x-on:show-loading.window="show($event.detail.message)"
    x-on:hide-loading.window="hide()"
    @keydown.escape.window="hide()"
    id="loading-notification"
>
    <div 
        x-show="showLoading" 
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-4 right-4 z-50"
        style="display: none;"
        
    >
        <div class="min-w-[300px] bg-slate-700 shadow-lg rounded-lg pointer-events-auto">
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="animate-spin text-blue-500 text-2xl">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-base font-medium text-white " x-text="message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button
                            @click="hide()"
                            class="text-white hover:text-blue-500 focus:outline-none "
                        >
                            <span class="sr-only">Cerrar</span>
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>