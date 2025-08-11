<div
    x-data="{
        estado: navigator.onLine ? 'online' : 'offline',
        setEstadoOnline() {
            this.estado = 'conectando';
            setTimeout(() => this.estado = 'online', 800);
        },
        setEstadoOffline() {
            this.estado = 'offline';
        },
        init() {
            window.addEventListener('online', this.setEstadoOnline.bind(this));
            window.addEventListener('offline', this.setEstadoOffline.bind(this));
        }
    }"
    x-init="init()"
    class="mx-4"
>
    <button
        class="text-xs font-semibold px-3 py-1 rounded-full shadow transition-all duration-300 text-black"
        :class="{
            'bg-green-300': estado === 'online',
            'bg-yellow-300 animate-pulse': estado === 'conectando',
            'bg-red-300': estado === 'offline',
        }"
        x-text="estado === 'online' ? 'Online' : (estado === 'conectando' ? 'Cargando...' : 'Offline')"
        disabled
    ></button>
</div>
