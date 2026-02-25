<div  wire:ignore x-data="{
    toasts: [],
    toastQueue: [],
    isProcessing: false,
    count: 0,
    minDelay: 1000,

    add(event) {
        this.count++;
        let toast = {
            id: this.count,
            message: event.detail.message,
            type: event.detail.type,
            duration: event.detail.duration || 4000
        };

        this.toastQueue.push(toast);
        this.processQueue();
    },

    processQueue() {
        if (this.isProcessing || this.toastQueue.length === 0) {
            return;
        }

        this.isProcessing = true;
        let toast = this.toastQueue.shift();
        this.toasts.push(toast);

        setTimeout(() => {
            this.isProcessing = false;
            this.processQueue();
        }, this.minDelay);
    },

    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
}" x-on:add-toast.window="add($event)"  style="position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); z-index: 9999; pointer-events: none;"
    class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[9999] pointer-events-none">

    <div class="flex flex-col gap-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-data="{ mostrarToast: true }"
                x-show="mostrarToast"
                x-init="setTimeout(() => { mostrarToast = false }, toast.duration)"

                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"

                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"

                @transitionend.opacity="!mostrarToast && remove(toast.id)"

                class="min-w-[300px] bg-slate-700 shadow-lg rounded-lg pointer-events-auto">
                <div class="p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <template x-if="toast.type === 'success'">
                                <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                            </template>
                            <template x-if="toast.type === 'error'">
                                <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                            </template>
                            <template x-if="toast.type === 'warning'">
                                <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                            </template>
                            <template x-if="toast.type === 'info'">
                                <i class="fas fa-info-circle text-blue-500 text-2xl"></i>
                            </template>
                        </div>
                        <div class="flex-1 border-s border-default ps-3.5">
                            <p class="text-base font-medium text-white" x-text="toast.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button @click="remove(toast.id)"
                                class="text-gray-400 hover:text-white focus:outline-none">
                                <span class="sr-only">Cerrar</span>
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
