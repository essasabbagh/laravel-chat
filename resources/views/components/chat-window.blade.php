<div x-data="chatWindow('{{ $conversationId }}', '{{ $participantType }}', '{{ $participantId }}')"
     style="height: {{ $height }}px;"
     class="flex flex-col border rounded-lg overflow-hidden bg-white">
    <div class="flex-1 overflow-y-auto p-4 space-y-2" x-ref="messages">
        <template x-for="message in messages" :key="message.id">
            <div :class="message.sender_id == participantId ? 'flex justify-end' : 'flex justify-start'" class="mb-3">
                <div :class="message.sender_id == participantId ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-800'"
                     class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg">
                    <p class="text-sm" x-text="message.body"></p>
                    <p class="text-xs text-right mt-1 opacity-75" x-text="message.created_at"></p>
                </div>
            </div>
        </template>
    </div>
    <div class="border-t p-3 flex gap-2">
        <input x-model="newMessage" @keyup.enter="sendMessage" type="text" placeholder="Type a message..."
               class="flex-1 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button @click="sendMessage" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-600">
            Send
        </button>
    </div>
</div>

<script>
    function chatWindow(conversationId, participantType, participantId) {
        return {
            messages: [],
            newMessage: '',
            conversationId: conversationId,
            participantType: participantType,
            participantId: participantId,
            init() {
                this.fetchMessages();
                this.listenForNewMessages();
            },
            fetchMessages() {
                fetch('/api/chat/conversations/' + this.conversationId + '/messages?participant_type=' + this.participantType + '&participant_id=' + this.participantId)
                    .then(r => r.json())
                    .then(data => { this.messages = data.data || data; });
            },
            sendMessage() {
                if (!this.newMessage.trim()) return;
                fetch('/api/chat/conversations/' + this.conversationId + '/messages', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        body: this.newMessage,
                        sender_type: this.participantType,
                        sender_id: this.participantId,
                    })
                }).then(r => r.json()).then(msg => {
                    this.messages.unshift(msg);
                    this.newMessage = '';
                });
            },
            listenForNewMessages() {
                if (typeof Echo !== 'undefined') {
                    Echo.channel('chat.conversation.' + this.conversationId)
                        .listen('MessageSent', (e) => {
                            this.messages.unshift(e);
                        });
                }
            }
        }
    }
</script>
