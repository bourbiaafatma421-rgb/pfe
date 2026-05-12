{{-- resources/views/components/messaging/inbox.blade.php --}}
{{-- Usage : <x-messaging.inbox /> dans n'importe quelle vue authentifiée --}}

<div
    x-data="messagingInbox()"
    x-init="init()"
    class="flex h-[600px] border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm"
>
    {{-- ── Panneau gauche : liste des conversations ── --}}
    <aside class="w-72 border-r border-gray-100 flex flex-col">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 text-sm">Messages</h2>
            <span
                x-show="totalUnread > 0"
                x-text="totalUnread"
                class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 font-medium"
            ></span>
        </div>

        {{-- Bouton nouveau message (RH seulement) --}}
        @if(auth()->user()->hasRole('RH'))
        <div class="px-4 py-2 border-b border-gray-100">
            <button
                @click="openNewConversationModal()"
                class="w-full text-xs text-blue-600 hover:text-blue-800 font-medium text-left"
            >
                + Nouvelle conversation
            </button>
        </div>
        @endif

        {{-- Liste --}}
        <ul class="flex-1 overflow-y-auto divide-y divide-gray-50">
            <template x-if="conversations.length === 0">
                <li class="px-4 py-8 text-center text-sm text-gray-400">Aucun message</li>
            </template>
            <template x-for="conv in conversations" :key="conv.id">
                <li
                    @click="openConversation(conv)"
                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
                    :class="{ 'bg-blue-50': activeConversation?.id === conv.id }"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm flex-shrink-0"
                             x-text="initials(conv.other)">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-800 truncate"
                                      x-text="conv.other.first_name + ' ' + conv.other.last_name"></span>
                                <span class="text-xs text-gray-400"
                                      x-text="conv.last_message ? relativeTime(conv.last_message.created_at) : ''"></span>
                            </div>
                            <p class="text-xs text-gray-500 truncate mt-0.5"
                               x-text="conv.last_message?.body ?? 'Démarrer la conversation'"></p>
                        </div>
                        <span
                            x-show="conv.unread_count > 0"
                            x-text="conv.unread_count"
                            class="ml-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0"
                        ></span>
                    </div>
                </li>
            </template>
        </ul>
    </aside>

    {{-- ── Panneau droit : zone de chat ── --}}
    <main class="flex-1 flex flex-col">
        {{-- Aucune conversation sélectionnée --}}
        <template x-if="!activeConversation">
            <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                Sélectionnez une conversation
            </div>
        </template>

        <template x-if="activeConversation">
            <div class="flex flex-col h-full">
                {{-- Header --}}
                <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm"
                         x-text="initials(activeConversation.other)"></div>
                    <span class="font-medium text-gray-800 text-sm"
                          x-text="activeConversation.other.first_name + ' ' + activeConversation.other.last_name"></span>
                </div>

                {{-- Messages --}}
                <div
                    class="flex-1 overflow-y-auto px-5 py-4 space-y-3"
                    x-ref="messageList"
                >
                    <template x-for="msg in messages" :key="msg.id">
                        <div
                            class="flex"
                            :class="msg.sender_id === {{ auth()->id() }} ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="max-w-[70%] px-4 py-2 rounded-2xl text-sm"
                                :class="msg.sender_id === {{ auth()->id() }}
                                    ? 'bg-indigo-600 text-white rounded-br-sm'
                                    : 'bg-gray-100 text-gray-800 rounded-bl-sm'"
                            >
                                <p x-text="msg.body"></p>
                                <p class="text-xs mt-1 opacity-60 text-right"
                                   x-text="relativeTime(msg.created_at)"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Indicateur chargement --}}
                    <div x-show="loadingMessages" class="text-center text-xs text-gray-400 py-2">
                        Chargement…
                    </div>
                </div>

                {{-- Barre de saisie --}}
                <div class="px-4 py-3 border-t border-gray-100 flex items-end gap-3">
                    <textarea
                        x-model="draft"
                        @keydown.enter.prevent="if(!$event.shiftKey) sendMessage()"
                        rows="1"
                        placeholder="Écrire un message… (Entrée pour envoyer)"
                        class="flex-1 resize-none border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition"
                        :disabled="sending"
                    ></textarea>
                    <button
                        @click="sendMessage()"
                        :disabled="!draft.trim() || sending"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white rounded-xl px-4 py-2 text-sm font-medium transition"
                    >
                        Envoyer
                    </button>
                </div>
            </div>
        </template>
    </main>
</div>

<script>
function messagingInbox() {
    return {
        conversations:      [],
        activeConversation: null,
        messages:           [],
        draft:              '',
        sending:            false,
        loadingMessages:    false,
        totalUnread:        0,
        pollInterval:       null,

        async init() {
            await this.loadConversations();
            await this.loadUnreadCount();
            // Polling léger toutes les 15 s (remplaçable par WebSockets/Echo)
            this.pollInterval = setInterval(() => {
                this.loadConversations();
                this.loadUnreadCount();
                if (this.activeConversation) this.loadMessages(this.activeConversation.id, false);
            }, 15_000);
        },

        async loadConversations() {
            try {
                const res = await fetch('/messaging/conversations', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                this.conversations = await res.json();
            } catch (e) { console.error(e); }
        },

        async loadUnreadCount() {
            try {
                const res = await fetch('/messaging/unread-count', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.totalUnread = data.unread;
            } catch (e) {}
        },

        async openConversation(conv) {
            this.activeConversation = conv;
            await this.loadMessages(conv.id);
        },

        async loadMessages(conversationId, scrollBottom = true) {
            this.loadingMessages = true;
            try {
                const res = await fetch(`/messaging/conversations/${conversationId}/messages`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                // L'API retourne paginé DESC → on inverse pour affichage
                this.messages = (data.data ?? []).reverse();
                if (scrollBottom) this.$nextTick(() => this.scrollToBottom());
                // Mettre à jour le badge de cette conversation
                const c = this.conversations.find(c => c.id === conversationId);
                if (c) c.unread_count = 0;
                await this.loadUnreadCount();
            } catch (e) { console.error(e); }
            this.loadingMessages = false;
        },

        async sendMessage() {
            if (!this.draft.trim() || this.sending) return;
            this.sending = true;
            try {
                const res = await fetch(
                    `/messaging/conversations/${this.activeConversation.id}/messages`,
                    {
                        method:  'POST',
                        headers: {
                            'Content-Type':     'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ body: this.draft }),
                    }
                );
                const msg = await res.json();
                this.messages.push(msg);
                this.draft = '';
                this.$nextTick(() => this.scrollToBottom());
                await this.loadConversations();
            } catch (e) { console.error(e); }
            this.sending = false;
        },

        // Démarrer une conversation (RH → sélectionne un collaborateur)
        async openNewConversationModal() {
            const collabId = prompt('ID du collaborateur :');
            if (!collabId) return;
            try {
                const res = await fetch('/messaging/conversations', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ collaborateur_id: collabId }),
                });
                const data = await res.json();
                await this.loadConversations();
                const conv = this.conversations.find(c => c.id === data.conversation_id);
                if (conv) await this.openConversation(conv);
            } catch (e) { console.error(e); }
        },

        scrollToBottom() {
            const el = this.$refs.messageList;
            if (el) el.scrollTop = el.scrollHeight;
        },

        initials(user) {
            return ((user.first_name?.[0] ?? '') + (user.last_name?.[0] ?? '')).toUpperCase();
        },

        relativeTime(dateStr) {
            const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
            if (diff < 60)   return 'À l\'instant';
            if (diff < 3600) return `${Math.floor(diff / 60)} min`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} h`;
            return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
        },
    };
}
</script>