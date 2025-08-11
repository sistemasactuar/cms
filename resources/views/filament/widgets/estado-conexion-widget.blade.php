<div
    x-data="{ online: navigator.onLine }"
    x-init="
        window.addEventListener('online', () => online = true);
        window.addEventListener('offline', () => online = false);
    "
    class="flex items-center gap-2 justify-end pr-4"
>
    <div class="text-sm font-medium">
        <span
            :class="online ? 'text-green-600' : 'text-red-600'"
            class="font-bold"
        >
            <template x-if="online">🟢 Online</template>
            <template x-if="!online">🔴 Offline</template>
        </span>
    </div>
</div>
