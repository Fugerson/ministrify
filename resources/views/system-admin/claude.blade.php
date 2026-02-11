@extends('layouts.system-admin')

@section('title', 'Claude Code')

@section('content')
<div x-data="claudeChat()" class="flex flex-col" style="height: calc(100vh - 8rem);">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Claude Code</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">AI-ассистент для работы с кодом проекта</p>
            </div>
        </div>
        <button @click="clearSession()" class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
            Очистить сессию
        </button>
    </div>

    {{-- Messages --}}
    <div x-ref="messages" class="flex-1 overflow-y-auto space-y-4 pb-4 scroll-smooth">

        {{-- Welcome --}}
        <template x-if="messages.length === 0 && !loading">
            <div class="flex flex-col items-center justify-center h-full text-center px-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-amber-600 flex items-center justify-center mb-4">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Claude Code Assistant</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mb-6">
                    Могу читать файлы, искать код, предлагать правки и выполнять команды.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-lg w-full">
                    <button @click="sendQuick('Покажи структуру app/Models/')" class="text-left p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Структура моделей</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Показать содержимое app/Models/</p>
                    </button>
                    <button @click="sendQuick('Покажи последние коммиты (git log --oneline -20)')" class="text-left p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Git история</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Последние 20 коммитов</p>
                    </button>
                    <button @click="sendQuick('Найди все TODO в коде')" class="text-left p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Найти TODO</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Поиск TODO комментариев</p>
                    </button>
                    <button @click="sendQuick('Покажи git status')" class="text-left p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Git статус</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Текущие изменения в репозитории</p>
                    </button>
                </div>
            </div>
        </template>

        {{-- Message list --}}
        <template x-for="(msg, index) in messages" :key="index">
            <div>
                {{-- User message --}}
                <template x-if="msg.role === 'user'">
                    <div class="flex justify-end">
                        <div class="max-w-[80%] bg-indigo-600 text-white rounded-2xl rounded-br-md px-4 py-3">
                            <p class="text-sm whitespace-pre-wrap" x-text="msg.content"></p>
                        </div>
                    </div>
                </template>

                {{-- Assistant text --}}
                <template x-if="msg.role === 'assistant' && msg.type === 'text'">
                    <div class="flex justify-start">
                        <div class="max-w-[85%] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                            <div class="text-sm prose prose-sm dark:prose-invert max-w-none" x-html="msg.html"></div>
                        </div>
                    </div>
                </template>

                {{-- Pending edit --}}
                <template x-if="msg.role === 'assistant' && msg.type === 'pending_edit'">
                    <div class="flex justify-start">
                        <div class="max-w-[90%] bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-2xl px-4 py-3">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span class="font-semibold text-amber-800 dark:text-amber-300 text-sm">Предлагаемое изменение</span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                                <span class="font-mono text-xs bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded" x-text="msg.file"></span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="msg.description"></p>
                            <pre class="text-xs bg-gray-900 text-gray-100 rounded-lg p-3 overflow-x-auto mb-3 max-h-64"><code x-text="msg.diff"></code></pre>

                            <div class="flex gap-2" x-show="msg.status === 'pending'">
                                <button @click="applyEdit(msg, false)" class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                    Применить
                                </button>
                                <button @click="applyEdit(msg, true)" class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    Применить + Clear Cache
                                </button>
                                <button @click="cancelEditAction(msg)" class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                                    Отмена
                                </button>
                            </div>
                            <p x-show="msg.status === 'applied'" class="text-sm text-green-600 dark:text-green-400 font-medium">&#10003; Применено</p>
                            <p x-show="msg.status === 'cancelled'" class="text-sm text-gray-500">Отменено</p>
                            <p x-show="msg.status === 'error'" class="text-sm text-red-600 dark:text-red-400" x-text="msg.errorMessage"></p>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Loading --}}
        <template x-if="loading">
            <div class="flex justify-start">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-2">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Claude думает...</span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Input --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <form @submit.prevent="send()" class="flex gap-3">
            <div class="flex-1 relative">
                <textarea x-ref="input"
                    x-model="input"
                    @keydown.enter.prevent="if (!$event.shiftKey) send()"
                    placeholder="Спросите что-нибудь о коде..."
                    rows="1"
                    class="w-full resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 pr-12 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    style="max-height: 150px"
                    @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 150) + 'px'"
                    :disabled="loading"></textarea>
            </div>
            <button type="submit"
                    :disabled="loading || !input.trim()"
                    class="px-4 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl transition-colors flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function claudeChat() {
    return {
        messages: [],
        input: '',
        loading: false,

        sendQuick(text) {
            this.input = text;
            this.send();
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.loading = true;

            // Reset textarea height
            this.$nextTick(() => {
                this.$refs.input.style.height = 'auto';
                this.scrollToBottom();
            });

            try {
                const response = await fetch('{{ route("system.claude.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                for (const action of (data.actions || [])) {
                    if (action.type === 'text') {
                        this.messages.push({
                            role: 'assistant',
                            type: 'text',
                            content: action.content,
                            html: this.renderMarkdown(action.content),
                        });
                    } else if (action.type === 'pending_edit') {
                        this.messages.push({
                            role: 'assistant',
                            type: 'pending_edit',
                            edit_id: action.edit_id,
                            file: action.file,
                            description: action.description,
                            diff: action.diff,
                            status: 'pending',
                        });
                    }
                }

                if (!data.actions || data.actions.length === 0) {
                    this.messages.push({
                        role: 'assistant',
                        type: 'text',
                        content: '(нет ответа)',
                        html: '<em>(нет ответа)</em>',
                    });
                }
            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    type: 'text',
                    content: 'Ошибка: ' + error.message,
                    html: '<span class="text-red-500">Ошибка: ' + this.escapeHtml(error.message) + '</span>',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async applyEdit(msg, clearCache) {
            const url = clearCache
                ? '{{ url("system-admin/claude/apply-clear") }}/' + msg.edit_id
                : '{{ url("system-admin/claude/apply") }}/' + msg.edit_id;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    msg.status = 'applied';
                } else {
                    msg.status = 'error';
                    msg.errorMessage = data.message;
                }
            } catch (error) {
                msg.status = 'error';
                msg.errorMessage = error.message;
            }
        },

        async cancelEditAction(msg) {
            try {
                await fetch('{{ url("system-admin/claude/cancel") }}/' + msg.edit_id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                msg.status = 'cancelled';
            } catch (error) {
                // Ignore
            }
        },

        async clearSession() {
            try {
                await fetch('{{ route("system.claude.clear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                this.messages = [];
            } catch (error) {
                // Ignore
            }
        },

        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        renderMarkdown(text) {
            let html = this.escapeHtml(text);

            // Code blocks: ```lang\n...\n```
            html = html.replace(/```(\w*)\n([\s\S]*?)```/g, (match, lang, code) => {
                return `<pre class="bg-gray-900 text-gray-100 rounded-lg p-3 overflow-x-auto text-xs my-2"><code>${code.trim()}</code></pre>`;
            });

            // Inline code
            html = html.replace(/`([^`]+)`/g, '<code class="bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs">$1</code>');

            // Bold
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

            // Italic
            html = html.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');

            // Line breaks
            html = html.replace(/\n/g, '<br>');

            return html;
        },
    };
}
</script>
@endpush
