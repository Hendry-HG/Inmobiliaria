{{-- Sistema de mensajería en vivo entre usuarios --}}
@extends('layouts.dashboard')

@section('title', 'Chat en Vivo')
@section('header', 'Mensajes')

@push('css')
<style>
    [x-cloak] { display: none !important; }

    .chat-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .chat-bg { background-color: #efeae2; }

    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    .message-enter { animation: popIn 0.3s ease-out; }
    @keyframes popIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }

    .bubble { max-width: 75%; padding: 8px 12px; font-size: 0.95rem; border-radius: 12px; word-break: break-word; }
    .bubble-sent { background: #c5a059; color: white; border-radius: 12px 12px 0 12px; }
    .bubble-received { background: white; color: #111b21; border-radius: 12px 12px 12px 0; border: 1px solid #e5e7eb; }

    .msg-wrapper { position: relative; margin-bottom: 16px; display: flex; }
    .msg-sent { justify-content: flex-end; }
    .msg-received { justify-content: flex-start; }

    .msg-actions {
        position: absolute;
        top: -8px;
        background: #1f2937;
        border-radius: 6px;
        padding: 4px 6px;
        display: none;
        gap: 8px;
        z-index: 10;
        white-space: nowrap;
    }
    .msg-sent .msg-actions { right: 0; }
    .msg-received .msg-actions { left: 0; }
    .msg-wrapper:hover .msg-actions { display: flex; }

    .msg-actions button {
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        cursor: pointer;
        background: transparent;
        border: none;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .msg-actions button:hover { background: rgba(255,255,255,0.2); }

    .typing-dot { animation: bounce 1.4s infinite; background: #90949c; width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin: 0 1px; }
    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }
    @keyframes bounce { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }

    .conversation-item { transition: all 0.2s; }
    .conversation-item:hover .delete-conv-btn { opacity: 1; }
    .delete-conv-btn { opacity: 0; transition: opacity 0.2s; }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.2s ease-out;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .modal-container {
        background: white;
        border-radius: 16px;
        max-width: 400px;
        width: 90%;
        padding: 24px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        animation: slideUp 0.3s ease-out;
    }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .btn-nuevo-chat {
        background: #0f172a;
        color: white;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-nuevo-chat:hover { background: #1e293b; }

    .btn-clientes {
        background: #16a34a;
        color: white;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-clientes:hover { background: #15803d; }

    /* Desktop */
    @media (min-width: 769px) {
        .sidebar-container {
            width: 320px;
            flex-shrink: 0;
            position: relative;
            border-right: 1px solid #e2e8f0;
            background: white;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .chat-container {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            background: #f8fafc;
        }
    }

    /* Móvil */
    @media (max-width: 768px) {
        .bubble { max-width: 85%; }
        .conversation-item { padding: 12px; }

        .sidebar-mobile {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 30;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .sidebar-mobile.open {
            transform: translateX(0);
        }

        .chat-mobile {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f8fafc;
            z-index: 20;
            transform: translateX(0);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .chat-mobile.hidden {
            transform: translateX(100%);
        }

        .back-button {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 15;
            background: white;
            border-radius: 9999px;
            padding: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    }
</style>
@endpush

@section('content')
<div class="chat-wrapper" x-data="chatApp()" x-init="init()">

    {{-- SIDEBAR (Desktop) --}}
    <div x-show="!isMobile" class="sidebar-container float-left">
        <div class="p-4 border-b">
            <h3 class="font-bold text-xl mb-3">Chats</h3>
            <div class="flex gap-2">
                <button x-show="isCliente" @click="showAsesoresModal = true" class="btn-nuevo-chat w-full">
                    <i class="ph-bold ph-plus"></i> Nuevo Chat con Asesor
                </button>
                <button x-show="isAsesor" @click="showClientesModal = true" class="btn-clientes w-full">
                    <i class="ph-bold ph-users"></i> Ver Clientes con Citas
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto custom-scroll" id="conversationsListDesktop"></div>
    </div>

    {{-- SIDEBAR (Móvil) --}}
    <div x-show="isMobile"
         x-cloak
         :class="{'sidebar-mobile': true, 'open': activeView === 'conversations'}">
        <div class="p-4 border-b">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-xl">Chats</h3>
                <button @click="activeView = 'chat'" class="p-2 text-slate-500">
                    <i class="ph-bold ph-x text-xl"></i>
                </button>
            </div>
            <div class="flex gap-2">
                <button x-show="isCliente" @click="showAsesoresModal = true" class="btn-nuevo-chat flex-1">
                    <i class="ph-bold ph-plus"></i> Nuevo Chat
                </button>
                <button x-show="isAsesor" @click="showClientesModal = true" class="btn-clientes flex-1">
                    <i class="ph-bold ph-users"></i> Clientes
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto custom-scroll" id="conversationsListMobile"></div>
    </div>

    {{-- CHAT AREA --}}
    <div :class="{
        'chat-container': true,
        'chat-mobile': isMobile,
        'hidden': isMobile && activeView !== 'chat'
    }">

        <!-- Botón volver (móvil) -->
        <div x-show="isMobile && currentConversation" class="back-button">
            <button @click="activeView = 'conversations'" class="text-slate-600">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </button>
        </div>

        <!-- Sin conversación -->
        <div x-show="!currentConversation" class="flex-1 flex items-center justify-center">
            <div class="text-center p-4">
                <i class="ph-bold ph-chat-text text-5xl text-slate-300 mb-3"></i>
                <h3 class="text-xl font-bold text-slate-700">Selecciona un chat</h3>
                <p class="text-slate-500 text-sm mt-1">o inicia una nueva conversación</p>
                <div class="flex gap-2 mt-4 justify-center flex-wrap">
                    <button x-show="isCliente" @click="showAsesoresModal = true" class="btn-nuevo-chat px-4 py-2 rounded-lg text-sm">
                        <i class="ph-bold ph-plus"></i> Nuevo Chat con Asesor
                    </button>
                    <button x-show="isAsesor" @click="showClientesModal = true" class="btn-clientes px-4 py-2 rounded-lg text-sm">
                        <i class="ph-bold ph-users"></i> Ver Clientes
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat activo -->
        <template x-if="currentConversation">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div class="px-4 py-3 bg-white border-b flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <img :src="currentConversation.other_user.avatar" class="w-10 h-10 rounded-full object-cover" alt="Avatar">
                        <div>
                            <h4 class="font-bold text-sm" x-text="currentConversation.other_user.name"></h4>
                            <span x-show="currentConversation.other_user.is_online" class="text-green-600 text-xs">● En línea</span>
                            <span x-show="!currentConversation.other_user.is_online" class="text-slate-400 text-xs">○ Desconectado</span>
                        </div>
                    </div>
                    <button @click="showDeleteConversationModal = true" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </div>

                <!-- Mensajes -->
                <div class="flex-1 overflow-y-auto custom-scroll p-4 chat-bg" id="messagesContainer">
                    <div id="messagesList"></div>
                    <div x-show="isOtherTyping" class="flex justify-start">
                        <div class="bubble bubble-received px-3 py-2">
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <form @submit.prevent="sendOrUpdateMessage" class="p-3 bg-white border-t flex-shrink-0">
                    <div class="flex gap-2">
                        <input type="text" x-model="editingMessage" @input="handleTyping"
                               placeholder="Escribe un mensaje..."
                               class="flex-1 border rounded-full px-4 py-2 text-sm focus:outline-none focus:border-mso-gold">
                        <button type="submit" :disabled="!editingMessage.trim() || sending"
                                class="bg-mso-gold text-white px-4 py-2 rounded-full hover:bg-mso-blue transition disabled:opacity-50">
                            <i class="ph-bold" :class="editingMessageId ? 'ph-pencil-simple' : 'ph-paper-plane-right'"></i>
                            <span x-text="editingMessageId ? 'Editar' : 'Enviar'" class="ml-1 hidden sm:inline"></span>
                        </button>
                        <button x-show="editingMessageId" type="button" @click="cancelEdit"
                                class="bg-slate-300 text-slate-700 px-4 py-2 rounded-full hover:bg-slate-400 transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </div>

    {{-- MODALES --}}
    <!-- Modal Asesores -->
    <div x-show="showAsesoresModal" x-cloak class="modal-overlay" @click.away="showAsesoresModal = false">
        <div class="modal-container">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">Seleccionar Asesor</h3>
                <button @click="showAsesoresModal = false"><i class="ph-bold ph-x text-xl"></i></button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <template x-for="asesor in asesoresDisponibles" :key="asesor.id">
                    <div @click="startConversationWithAsesor(asesor.id)" class="p-3 border-b hover:bg-slate-50 cursor-pointer flex items-center gap-3">
                        <img :src="asesor.avatar" class="w-12 h-12 rounded-full object-cover" alt="Asesor">
                        <div class="flex-1">
                            <p class="font-bold" x-text="asesor.name"></p>
                            <p class="text-xs text-slate-500" x-text="asesor.specialization || 'Asesor Inmobiliario'"></p>
                        </div>
                        <i class="ph-bold ph-chat-circle text-mso-gold"></i>
                    </div>
                </template>
                <div x-show="asesoresDisponibles.length === 0" class="text-center py-6">
                    <p class="text-slate-500">No hay asesores disponibles</p>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button @click="showAsesoresModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Clientes -->
    <div x-show="showClientesModal" x-cloak class="modal-overlay" @click.away="showClientesModal = false">
        <div class="modal-container">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">Clientes con Citas</h3>
                <button @click="showClientesModal = false"><i class="ph-bold ph-x text-xl"></i></button>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <template x-for="cliente in clientesDisponibles" :key="cliente.id">
                    <div @click="startConversationWithCliente(cliente.id)" class="p-3 border-b hover:bg-slate-50 cursor-pointer flex items-center gap-3">
                        <img :src="cliente.avatar" class="w-12 h-12 rounded-full object-cover" alt="Cliente">
                        <div class="flex-1">
                            <p class="font-bold" x-text="cliente.name"></p>
                            <p class="text-xs text-slate-500" x-text="cliente.email"></p>
                        </div>
                        <i class="ph-bold ph-chat-circle text-mso-gold"></i>
                    </div>
                </template>
                <div x-show="clientesDisponibles.length === 0" class="text-center py-6">
                    <p class="text-slate-500">No hay clientes con citas programadas</p>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button @click="showClientesModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Conversación -->
    <div x-show="showDeleteConversationModal" x-cloak class="modal-overlay" @click.away="showDeleteConversationModal = false">
        <div class="modal-container">
            <h3 class="text-lg font-bold mb-2">Eliminar conversación</h3>
            <p class="text-slate-600 text-sm mb-4">¿Estás seguro? Esta acción solo es visible para ti.</p>
            <div class="flex justify-end gap-2">
                <button @click="showDeleteConversationModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancelar</button>
                <button @click="confirmDeleteConversation" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Mensaje -->
    <div x-show="showDeleteMessageModal" x-cloak class="modal-overlay" @click.away="showDeleteMessageModal = false">
        <div class="modal-container">
            <h3 class="text-lg font-bold mb-2">Eliminar mensaje</h3>
            <p class="text-slate-600 text-sm mb-4">¿Estás seguro de que quieres eliminar este mensaje?</p>
            <div class="flex justify-end gap-2">
                <button @click="showDeleteMessageModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancelar</button>
                <button @click="confirmDeleteMessage" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function chatApp() {
        return {
            conversations: [],
            currentConversationId: null,
            currentConversation: null,
            messages: [],
            editingMessageId: null,
            editingMessage: '',
            sending: false,
            userId: {{ Auth::id() }},
            isAsesor: {{ $isAsesor ? 'true' : 'false' }},
            isCliente: {{ $isCliente ? 'true' : 'false' }},
            asesoresDisponibles: @json($asesoresDisponibles ?? []),
            clientesDisponibles: @json($clientesDisponibles ?? []),
            showAsesoresModal: false,
            showClientesModal: false,
            showDeleteConversationModal: false,
            showDeleteMessageModal: false,
            messageToDelete: null,
            conversationToDelete: null,
            loading: true,
            isMobile: window.innerWidth < 768,
            isOtherTyping: false,
            typingTimeout: null,
            echoChannel: null,
            echoInitialized: false,
            activeView: 'conversations',
            reconnectAttempts: 0,
            maxReconnectAttempts: 5,

            async init() {
                window.chatApp = this;

                window.addEventListener('resize', () => {
                    this.isMobile = window.innerWidth < 768;
                    if (!this.isMobile) this.activeView = 'conversations';
                });

                // Marcar usuario como online
                await this.updatePresence(true);

                // Marcar offline al cerrar
                window.addEventListener('beforeunload', () => {
                    this.updatePresence(false);
                });

                await this.fetchConversations();
                this.initEcho();
                this.loading = false;

                // Actualizar cada 30 segundos
                setInterval(() => this.fetchConversations(), 30000);
            },

            async updatePresence(isOnline) {
                try {
                    await fetch('/chat/presence', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ is_online: isOnline })
                    });
                } catch (e) {
                    console.error('Error updating presence:', e);
                }
            },

            async fetchConversations() {
                try {
                    const res = await fetch('/chat/conversations');
                    const data = await res.json();
                    if (data.success) {
                        this.conversations = data.conversations;
                        this.renderConversations();
                    }
                } catch (error) {
                    console.error('Error fetching conversations:', error);
                }
            },

            renderConversations() {
                const containerDesktop = document.getElementById('conversationsListDesktop');
                if (containerDesktop) {
                    containerDesktop.innerHTML = '';
                    this.renderConversationsInContainer(containerDesktop);
                }

                const containerMobile = document.getElementById('conversationsListMobile');
                if (containerMobile) {
                    containerMobile.innerHTML = '';
                    this.renderConversationsInContainer(containerMobile);
                }
            },

            renderConversationsInContainer(container) {
                if (this.conversations.length === 0) {
                    container.innerHTML = `<div class="text-center py-10"><p class="text-sm text-slate-500">No hay conversaciones</p></div>`;
                    return;
                }

                this.conversations.forEach(conv => {
                    const div = document.createElement('div');
                    div.className = `conversation-item p-3 border-b cursor-pointer ${this.currentConversationId === conv.id ? 'bg-blue-50' : 'hover:bg-slate-50'}`;
                    div.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                <img src="${this.escapeHtml(conv.other_user.avatar)}" class="w-10 h-10 rounded-full object-cover" alt="Avatar">
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm truncate">${this.escapeHtml(conv.other_user.name)}</div>
                                    <div class="text-xs text-slate-500 truncate">${this.escapeHtml(conv.last_message || '...')}</div>
                                </div>
                                ${conv.unread_count > 0 ? `<span class="bg-mso-gold text-white text-xs px-2 py-0.5 rounded-full">${conv.unread_count}</span>` : ''}
                            </div>
                            <button onclick="event.stopPropagation(); window.chatApp.showDeleteConversationPrompt(${conv.id})"
                                    class="delete-conv-btn text-slate-400 hover:text-red-500 p-2 rounded-full ml-2">
                                <i class="ph-bold ph-trash text-sm"></i>
                            </button>
                        </div>
                    `;
                    div.querySelector('.flex-1').onclick = () => this.selectConversation(conv.id);
                    container.appendChild(div);
                });
            },

            renderMessages() {
                const container = document.getElementById('messagesList');
                if (!container) return;
                container.innerHTML = '';
                if (this.messages.length === 0) return;

                this.messages.forEach(msg => {
                    const isSent = msg.user_id === this.userId;
                    const div = document.createElement('div');
                    div.className = `msg-wrapper ${isSent ? 'msg-sent' : 'msg-received'} message-enter`;
                    div.innerHTML = `
                        ${isSent ? `
                            <div class="msg-actions">
                                <button onclick="window.chatApp.editMessagePrompt(${msg.id}, '${this.escapeHtml(msg.content).replace(/'/g, "\\'")}')">
                                    <i class="ph-bold ph-pencil-simple"></i> Editar
                                </button>
                                <button onclick="window.chatApp.showDeleteMessagePrompt(${msg.id})">
                                    <i class="ph-bold ph-trash"></i> Eliminar
                                </button>
                            </div>
                        ` : ''}
                        <div class="bubble ${isSent ? 'bubble-sent' : 'bubble-received'}">
                            ${this.escapeHtml(msg.content)}
                            <div class="text-[9px] opacity-70 text-right mt-1">
                                ${msg.formatted_time || ''}
                                ${isSent ? (msg.is_read ? '✓✓' : '✓') : ''}
                            </div>
                        </div>
                    `;
                    container.appendChild(div);
                });
                this.scrollToBottom();
            },

            async loadMessages(conversationId) {
                try {
                    const res = await fetch(`/chat/messages/${conversationId}`);
                    const data = await res.json();
                    if (data.success) {
                        this.messages = data.messages;
                        this.currentConversation = data.conversation;
                        this.renderMessages();
                        await this.markAsRead(conversationId);
                    }
                } catch (error) {
                    console.error('Error loading messages:', error);
                }
            },

            sendOrUpdateMessage() {
                if (this.sending) return;
                if (this.editingMessageId) {
                    this.updateMessage();
                } else {
                    this.sendMessage();
                }
            },

            async sendMessage() {
                if (!this.editingMessage.trim() || !this.currentConversationId) return;
                if (this.sending) return;

                this.sending = true;
                const content = this.editingMessage.trim();
                const tempId = Date.now();

                const tempMessage = {
                    id: tempId,
                    content: content,
                    user_id: this.userId,
                    formatted_time: new Date().toLocaleTimeString(),
                    is_temp: true
                };

                this.messages.push(tempMessage);
                this.editingMessage = '';
                this.renderMessages();

                try {
                    const res = await fetch(`/chat/send/${this.currentConversationId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ content })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const index = this.messages.findIndex(m => m.id === tempId);
                        if (index !== -1) this.messages[index] = data.message;
                        this.renderMessages();
                        this.fetchConversations();
                    } else {
                        // Error, eliminar mensaje temporal
                        this.messages = this.messages.filter(m => m.id !== tempId);
                        this.renderMessages();
                        if (data.errors) {
                            alert(Object.values(data.errors).flat().join('\n'));
                        } else {
                            alert(data.error || 'Error al enviar mensaje');
                        }
                    }
                } catch (error) {
                    this.messages = this.messages.filter(m => m.id !== tempId);
                    this.renderMessages();
                    console.error('Error sending message:', error);
                    alert('Error al enviar mensaje. Por favor, intenta de nuevo.');
                } finally {
                    this.sending = false;
                }
            },

            editMessagePrompt(messageId, content) {
                this.editingMessageId = messageId;
                this.editingMessage = content;
                document.querySelector('input[type="text"]')?.focus();
            },

            cancelEdit() {
                this.editingMessageId = null;
                this.editingMessage = '';
            },

            async updateMessage() {
                if (!this.editingMessage.trim() || !this.editingMessageId) return;
                if (this.sending) return;

                this.sending = true;
                try {
                    const res = await fetch(`/chat/message/${this.editingMessageId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ content: this.editingMessage.trim() })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const index = this.messages.findIndex(m => m.id === this.editingMessageId);
                        if (index !== -1) {
                            this.messages[index].content = this.editingMessage.trim();
                            this.renderMessages();
                        }
                        this.cancelEdit();
                        this.fetchConversations();
                    } else {
                        alert(data.error || 'Error al editar mensaje');
                    }
                } catch (e) {
                    console.error('Error editing message:', e);
                    alert('Error al editar mensaje');
                } finally {
                    this.sending = false;
                }
            },

            showDeleteMessagePrompt(messageId) {
                this.messageToDelete = messageId;
                this.showDeleteMessageModal = true;
            },

            async confirmDeleteMessage() {
                if (!this.messageToDelete) return;
                try {
                    const res = await fetch(`/chat/message/${this.messageToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.messages = this.messages.filter(m => m.id !== this.messageToDelete);
                        this.renderMessages();
                        this.fetchConversations();
                    } else {
                        alert(data.error || 'Error al eliminar mensaje');
                    }
                    this.showDeleteMessageModal = false;
                    this.messageToDelete = null;
                } catch (e) {
                    console.error('Error deleting message:', e);
                    alert('Error al eliminar mensaje');
                }
            },

            showDeleteConversationPrompt(conversationId) {
                this.conversationToDelete = conversationId;
                this.showDeleteConversationModal = true;
            },

            async confirmDeleteConversation() {
                if (!this.conversationToDelete) return;
                try {
                    const res = await fetch(`/chat/conversation/${this.conversationToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.conversations = this.conversations.filter(c => c.id !== this.conversationToDelete);
                        if (this.currentConversationId === this.conversationToDelete) {
                            this.currentConversationId = null;
                            this.currentConversation = null;
                            this.messages = [];
                            this.renderMessages();
                            if (this.isMobile) this.activeView = 'conversations';
                        }
                        this.renderConversations();
                    } else {
                        alert(data.error || 'Error al eliminar conversación');
                    }
                    this.showDeleteConversationModal = false;
                    this.conversationToDelete = null;
                } catch (e) {
                    console.error('Error deleting conversation:', e);
                    alert('Error al eliminar conversación');
                }
            },

            initEcho() {
                if (this.echoInitialized) return;
                if (typeof window.Echo !== 'undefined' && window.Echo?.channel) {
                    try {
                        this.echoChannel = window.Echo.channel(`chat.user.${this.userId}`);

                        this.echoChannel.listen('.NewMessage', (e) => {
                            if (e.conversation_id == this.currentConversationId) {
                                const exists = this.messages.some(m => m.id === e.message.id);
                                if (!exists) {
                                    this.messages.push(e.message);
                                    this.renderMessages();
                                    this.markAsRead(e.conversation_id);
                                }
                            }
                            this.fetchConversations();
                        });

                        this.echoChannel.listen('.MessageEdited', (e) => {
                            if (e.conversation_id == this.currentConversationId) {
                                const index = this.messages.findIndex(m => m.id === e.message_id);
                                if (index !== -1) {
                                    this.messages[index].content = e.new_content;
                                    this.renderMessages();
                                }
                            }
                            this.fetchConversations();
                        });

                        this.echoChannel.listen('.MessageDeleted', (e) => {
                            if (e.conversation_id == this.currentConversationId) {
                                this.messages = this.messages.filter(m => m.id !== e.message_id);
                                this.renderMessages();
                            }
                            this.fetchConversations();
                        });

                        this.echoChannel.listen('.UserTyping', (e) => {
                            if (e.conversation_id == this.currentConversationId && e.user_id !== this.userId) {
                                this.isOtherTyping = e.is_typing;
                            }
                        });

                        this.echoInitialized = true;
                        console.log(" Echo conectado correctamente");
                    } catch (error) {
                        console.error('Error initializing Echo:', error);
                        this.reconnectEcho();
                    }
                } else {
                    console.log('⏳ Esperando Echo...');
                    setTimeout(() => this.initEcho(), 1000);
                }
            },

            reconnectEcho() {
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                    console.log(`🔄 Reintentando conectar Echo (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
                    setTimeout(() => this.initEcho(), 3000);
                } else {
                    console.error('❌ No se pudo conectar Echo después de varios intentos');
                }
            },

            selectConversation(conversationId) {
                if (this.currentConversationId === conversationId) return;
                this.currentConversationId = conversationId;
                this.currentConversation = this.conversations.find(c => c.id === conversationId);
                this.loadMessages(conversationId);
                this.renderConversations();
                this.cancelEdit();
                if (this.isMobile) {
                    this.activeView = 'chat';
                }
            },

            handleTyping() {
                if (!this.currentConversationId) return;
                fetch(`/chat/typing/${this.currentConversationId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_typing: true })
                });
                if (this.typingTimeout) clearTimeout(this.typingTimeout);
                this.typingTimeout = setTimeout(() => {
                    fetch(`/chat/typing/${this.currentConversationId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ is_typing: false })
                    });
                }, 1000);
            },

            async startConversationWithAsesor(asesorId) {
                try {
                    const res = await fetch('/chat/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ asesor_id: asesorId })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showAsesoresModal = false;
                        await this.fetchConversations();
                        this.selectConversation(data.conversation_id);
                    } else {
                        alert(data.error || 'Error al iniciar conversación');
                    }
                } catch (e) {
                    console.error('Error starting conversation:', e);
                    alert('Error al iniciar conversación');
                }
            },

            async startConversationWithCliente(clienteId) {
                try {
                    const res = await fetch('/chat/start-cliente', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ cliente_id: clienteId })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showClientesModal = false;
                        await this.fetchConversations();
                        this.selectConversation(data.conversation_id);
                    } else {
                        alert(data.error || 'Error al iniciar conversación');
                    }
                } catch (e) {
                    console.error('Error starting conversation:', e);
                    alert('Error al iniciar conversación');
                }
            },

            async markAsRead(conversationId) {
                try {
                    await fetch(`/chat/read/${conversationId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const conv = this.conversations.find(c => c.id === conversationId);
                    if (conv) conv.unread_count = 0;
                    this.renderConversations();
                } catch (e) {
                    console.error('Error marking as read:', e);
                }
            },

            escapeHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            },

            scrollToBottom() {
                setTimeout(() => {
                    const container = document.getElementById('messagesContainer');
                    if (container) container.scrollTop = container.scrollHeight;
                }, 50);
            }
        }
    }
</script>
@endpush
