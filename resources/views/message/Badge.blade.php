{{--
    resources/views/components/messaging/badge.blade.php
    Badge cliquable dans la navbar — remplace votre icône mail actuelle.
    Usage : <x-messaging.badge />
--}}

<div x-data="messagingBadge()" x-init="init()" class="relative">
    {{-- Icône mail (votre design actuel) --}}
    <a href="{{ route('messaging.conversations.index') }}" class="relative inline-flex items-center p-2 rounded-full hover:bg-white/10 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>

        {{-- Badge rouge dynamique --}}
        <span
            x-show="count > 0"
            x-text="count > 99 ? '99+' : count"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow"
        ></span>
    </a>
</div>

<script>
function messagingBadge() {
    return {
        count: 0,
        async init() {
            await this.refresh();
            setInterval(() => this.refresh(), 20_000);
        },
        async refresh() {
            try {
                const res  = await fetch('/messaging/unread-count', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.count = data.unread ?? 0;
            } catch {}
        },
    };
}
</script>