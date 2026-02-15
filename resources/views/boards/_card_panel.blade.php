<!-- Card Slide-Over Panel -->
<div x-show="cardPanel.open" x-cloak class="fixed inset-0 z-50 overflow-hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40" @click="closePanel()" x-show="cardPanel.open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Panel -->
    <div class="absolute inset-y-0 right-0 flex max-w-full">
        <div x-show="cardPanel.open"
             x-transition:enter="transform transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-screen max-w-3xl">
            <div class="h-full bg-white dark:bg-gray-900 shadow-xl flex flex-col">
                <template x-if="cardPanel.loading">
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-gray-500">Завантаження...</div>
                    </div>
                </template>

                <template x-if="!cardPanel.loading && cardPanel.data">
                    <div class="flex-1 flex flex-col overflow-hidden">
                        <!-- Header -->
                        <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 pr-4">
                                    <!-- Status & Priority badges -->
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                              :class="{
                                                  'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': cardPanel.data.column_name === 'Нові',
                                                  'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300': cardPanel.data.column_name === 'До виконання',
                                                  'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300': cardPanel.data.column_name === 'В процесі',
                                                  'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300': cardPanel.data.column_name === 'Завершено'
                                              }"
                                              x-text="cardPanel.data.column_name"></span>

                                        <template x-if="cardPanel.data.card.priority">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium"
                                                  :class="{
                                                      'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300': cardPanel.data.card.priority === 'urgent',
                                                      'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300': cardPanel.data.card.priority === 'high',
                                                      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300': cardPanel.data.card.priority === 'medium',
                                                      'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400': cardPanel.data.card.priority === 'low'
                                                  }">
                                                <span class="w-1.5 h-1.5 rounded-full"
                                                      :class="{
                                                          'bg-red-500': cardPanel.data.card.priority === 'urgent',
                                                          'bg-orange-500': cardPanel.data.card.priority === 'high',
                                                          'bg-yellow-500': cardPanel.data.card.priority === 'medium',
                                                          'bg-gray-400': cardPanel.data.card.priority === 'low'
                                                      }"></span>
                                                <span x-text="{'urgent': 'Терміново', 'high': 'Високий', 'medium': 'Середній', 'low': 'Низький'}[cardPanel.data.card.priority]"></span>
                                            </span>
                                        </template>

                                        <template x-if="cardPanel.data.card.is_completed">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Завершено
                                            </span>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-mono text-gray-400 dark:text-gray-500 cursor-pointer hover:text-primary-500 transition-colors"
                                              @click="navigator.clipboard.writeText('#' + cardPanel.data.card.id)"
                                              title="Натисніть щоб скопіювати">
                                            #<span x-text="cardPanel.data.card.id"></span>
                                        </span>
                                        <input type="text" x-model="cardPanel.data.card.title"
                                               @blur="saveCardField('title', cardPanel.data.card.title)"
                                               class="flex-1 text-xl font-bold text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 focus:outline-none"
                                               :class="{ 'line-through opacity-60': cardPanel.data.card.is_completed }">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="toggleCardComplete()"
                                            class="p-2 rounded-lg transition-colors"
                                            :class="cardPanel.data.card.is_completed ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20'"
                                            title="Позначити як завершене (C)">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button @click="closePanel()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Body with scroll -->
                        <div class="flex-1 overflow-y-auto">
                            <div class="flex">
                                <!-- Main content column -->
                                <div class="flex-1 px-6 py-4 space-y-6 border-r border-gray-200 dark:border-gray-700">
                                    <!-- Description -->
                                    <div>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            Опис
                                        </label>
                                        <textarea x-model="cardPanel.data.card.description"
                                                  @blur="saveCardField('description', cardPanel.data.card.description)"
                                                  rows="4" placeholder="Додайте детальний опис завдання..."
                                                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white resize-none"></textarea>
                                    </div>

                                    <!-- Checklist -->
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                Чеклист
                                            </label>
                                            <template x-if="cardPanel.data.checklist.length > 0">
                                                <span class="text-xs text-gray-400" x-text="`${cardPanel.data.checklist.filter(i => i.is_completed).length}/${cardPanel.data.checklist.length}`"></span>
                                            </template>
                                        </div>

                                        <template x-if="cardPanel.data.checklist.length > 0">
                                            <div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mb-3">
                                                <div class="h-full bg-green-500 rounded-full transition-all"
                                                     :style="`width: ${(cardPanel.data.checklist.filter(i => i.is_completed).length / cardPanel.data.checklist.length) * 100}%`"></div>
                                            </div>
                                        </template>

                                        <div class="space-y-1">
                                            <template x-for="item in cardPanel.data.checklist" :key="item.id">
                                                <div class="flex items-center gap-2 group py-1">
                                                    <button @click="toggleChecklistItem(item)" class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
                                                            :class="item.is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'">
                                                        <template x-if="item.is_completed">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </template>
                                                    </button>
                                                    <span class="flex-1 text-sm" :class="item.is_completed ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'" x-text="item.title"></span>
                                                    <button @click="deleteChecklistItem(item)" class="p-1 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="mt-2" x-data="{ adding: false, newItem: '' }">
                                            <template x-if="!adding">
                                                <button @click="adding = true" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Додати пункт
                                                </button>
                                            </template>
                                            <template x-if="adding">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" x-model="newItem" @keydown.enter="addChecklistItem(newItem); newItem=''; adding=false"
                                                           @keydown.escape="adding=false" placeholder="Назва пункту..." autofocus
                                                           class="flex-1 px-2 py-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded text-sm dark:text-white">
                                                    <button @click="addChecklistItem(newItem); newItem=''; adding=false" class="px-2 py-1 bg-primary-600 text-white text-sm rounded">OK</button>
                                                    <button @click="adding=false" class="p-1 text-gray-400 hover:text-gray-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Attachments -->
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                Файли
                                                <template x-if="cardPanel.data.attachments && cardPanel.data.attachments.length > 0">
                                                    <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.attachments.length"></span>
                                                </template>
                                            </label>
                                            <label class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 cursor-pointer flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Додати
                                                <input type="file" class="hidden" @change="uploadAttachment($event)">
                                            </label>
                                        </div>

                                        <div class="space-y-2">
                                            <template x-for="file in cardPanel.data.attachments" :key="file.id">
                                                <div class="flex items-center gap-3 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg group">
                                                    <template x-if="file.is_image">
                                                        <a :href="file.url" target="_blank" class="w-10 h-10 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                                            <img :src="file.url" class="w-full h-full object-cover">
                                                        </a>
                                                    </template>
                                                    <template x-if="!file.is_image">
                                                        <div class="w-10 h-10 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                        </div>
                                                    </template>
                                                    <div class="flex-1 min-w-0">
                                                        <a :href="file.url" target="_blank" class="text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 truncate block" x-text="file.name"></a>
                                                        <span class="text-xs text-gray-400" x-text="file.size"></span>
                                                    </div>
                                                    <button @click="deleteAttachment(file)" class="p-1 text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 flex-shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="!cardPanel.data.attachments || cardPanel.data.attachments.length === 0">
                                                <p class="text-center text-gray-400 text-xs py-2">Немає файлів</p>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Related Cards -->
                                    <div x-data="{ showLinkSearch: false, linkSearch: '' }">
                                        <div class="flex items-center justify-between mb-3">
                                            <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                Пов'язані
                                                <template x-if="cardPanel.data.related_cards && cardPanel.data.related_cards.length > 0">
                                                    <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.related_cards.length"></span>
                                                </template>
                                            </label>
                                            <button @click="showLinkSearch = !showLinkSearch" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Зв'язати
                                            </button>
                                        </div>

                                        <template x-if="showLinkSearch">
                                            <div class="mb-3">
                                                <input type="text" x-model="linkSearch" placeholder="Пошук завдання..."
                                                       class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white mb-2">
                                                <div class="max-h-32 overflow-y-auto space-y-1">
                                                    <template x-for="card in (cardPanel.data.available_cards || []).filter(c => !linkSearch || c.title.toLowerCase().includes(linkSearch.toLowerCase())).slice(0, 10)" :key="card.id">
                                                        <button @click="addRelatedCard(card.id); showLinkSearch = false; linkSearch = ''"
                                                                class="w-full px-3 py-1.5 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded flex items-center gap-2 dark:text-white">
                                                            <span class="text-gray-400 text-xs" x-text="'#' + card.id"></span>
                                                            <span class="truncate" x-text="card.title"></span>
                                                            <span class="ml-auto text-xs text-gray-400 flex-shrink-0" x-text="card.column_name"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="space-y-1">
                                            <template x-for="related in cardPanel.data.related_cards || []" :key="related.id">
                                                <div class="flex items-center gap-2 group py-1">
                                                    <button @click="closePanel(); $nextTick(() => openCard(related.id))" class="flex-1 text-left flex items-center gap-2 text-sm hover:text-primary-600 dark:text-gray-300 min-w-0">
                                                        <span class="text-gray-400 text-xs flex-shrink-0" x-text="'#' + related.id"></span>
                                                        <span class="truncate" :class="related.is_completed ? 'line-through text-gray-400' : ''" x-text="related.title"></span>
                                                        <span class="ml-auto text-xs text-gray-400 flex-shrink-0" x-text="related.column_name"></span>
                                                    </button>
                                                    <button @click="removeRelatedCard(related)" class="p-1 text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 flex-shrink-0">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Comments -->
                                    <div>
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            Коментарі
                                            <template x-if="cardPanel.data.comments && cardPanel.data.comments.length > 0">
                                                <span class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs" x-text="cardPanel.data.comments.length"></span>
                                            </template>
                                        </label>

                                        <!-- Add comment -->
                                        <div class="mb-4" x-data="{ newComment: '', commentFiles: [], fileNames: [] }">
                                            <textarea x-model="newComment" rows="2" placeholder="Написати коментар..."
                                                      @keydown.cmd.enter="addCommentWithFiles(newComment, commentFiles); newComment=''; commentFiles=[]; fileNames=[]"
                                                      @keydown.ctrl.enter="addCommentWithFiles(newComment, commentFiles); newComment=''; commentFiles=[]; fileNames=[]"
                                                      class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white resize-none"></textarea>

                                            <!-- Selected files preview -->
                                            <template x-if="fileNames.length > 0">
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    <template x-for="(name, idx) in fileNames" :key="idx">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-400">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                            <span x-text="name" class="truncate max-w-[120px]"></span>
                                                            <button @click="commentFiles.splice(idx, 1); fileNames.splice(idx, 1)" class="hover:text-red-500">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>

                                            <div class="flex items-center gap-2 mt-2">
                                                <button @click="addCommentWithFiles(newComment, commentFiles); newComment=''; commentFiles=[]; fileNames=[]" :disabled="!newComment.trim()"
                                                        class="px-3 py-1.5 bg-primary-600 text-white text-sm rounded-lg disabled:opacity-50">
                                                    Коментувати
                                                </button>
                                                <label class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    <input type="file" multiple class="hidden" @change="
                                                        for (let f of $event.target.files) {
                                                            commentFiles.push(f);
                                                            fileNames.push(f.name);
                                                        }
                                                        $event.target.value = '';
                                                    ">
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Comments list -->
                                        <div class="space-y-4">
                                            <template x-for="comment in cardPanel.data.comments" :key="comment.id">
                                                <div class="flex gap-3 group" x-data="{ editing: false, editContent: comment.content }">
                                                    <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                                        <span class="text-primary-600 dark:text-primary-400 text-xs font-medium" x-text="comment.user_initial"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-medium text-gray-900 dark:text-white text-sm" x-text="comment.user_name"></span>
                                                            <span class="text-xs text-gray-500" x-text="comment.created_at"></span>
                                                            <template x-if="comment.is_edited">
                                                                <span class="text-xs text-gray-400">(ред.)</span>
                                                            </template>
                                                        </div>
                                                        <template x-if="!editing">
                                                            <p class="text-gray-600 dark:text-gray-300 text-sm mt-0.5 whitespace-pre-wrap" x-text="comment.content"></p>
                                                        </template>
                                                        <template x-if="editing">
                                                            <div class="mt-1">
                                                                <textarea x-model="editContent" rows="2"
                                                                          class="w-full px-2 py-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded text-sm dark:text-white resize-none"></textarea>
                                                                <div class="flex gap-2 mt-1">
                                                                    <button @click="updateComment(comment, editContent); editing=false" class="px-2 py-1 bg-primary-600 text-white text-xs rounded">Зберегти</button>
                                                                    <button @click="editing=false; editContent=comment.content" class="px-2 py-1 text-gray-500 text-xs">Скасувати</button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <!-- Comment attachments -->
                                                        <template x-if="comment.attachments && comment.attachments.length > 0">
                                                            <div class="flex flex-wrap gap-2 mt-2">
                                                                <template x-for="att in comment.attachments" :key="att.name">
                                                                    <a :href="att.url" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-400 hover:text-primary-600">
                                                                        <template x-if="att.is_image">
                                                                            <img :src="att.url" class="w-16 h-12 object-cover rounded">
                                                                        </template>
                                                                        <template x-if="!att.is_image">
                                                                            <span class="flex items-center gap-1">
                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                                                <span x-text="att.name" class="truncate max-w-[100px]"></span>
                                                                            </span>
                                                                        </template>
                                                                    </a>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <template x-if="comment.is_mine && !editing">
                                                        <div class="flex gap-1 opacity-0 group-hover:opacity-100">
                                                            <button @click="editing=true" class="p-1 text-gray-400 hover:text-gray-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                            <button @click="deleteComment(comment)" class="p-1 text-gray-400 hover:text-red-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="cardPanel.data.comments.length === 0">
                                                <p class="text-center text-gray-400 text-sm py-4">Немає коментарів</p>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Activity History -->
                                    <div x-data="{ showAll: false }">
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Історія
                                        </label>
                                        <div class="space-y-2">
                                            <template x-for="(activity, idx) in (showAll ? cardPanel.data.activities : (cardPanel.data.activities || []).slice(0, 5))" :key="activity.id">
                                                <div class="flex gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0 text-[10px]" x-text="activity.user_initial"></span>
                                                    <div class="flex-1 min-w-0">
                                                        <span class="font-medium text-gray-700 dark:text-gray-300" x-text="activity.user_name"></span>
                                                        <span x-text="activity.description || ({'updated': 'оновив', 'moved': 'перемістив', 'assigned': 'призначив', 'created': 'створив', 'completed': 'завершив'}[activity.action] || activity.action)"></span>
                                                        <template x-if="activity.field && !activity.description">
                                                            <span>
                                                                <span class="text-gray-400" x-text="({'title': 'назву', 'description': 'опис', 'priority': 'пріоритет', 'due_date': 'дедлайн', 'column_id': 'статус', 'epic_id': 'проєкт', 'assigned_to': 'виконавця'}[activity.field] || activity.field)"></span>
                                                                <template x-if="activity.old_value">
                                                                    <span>: <span class="line-through" x-text="activity.old_value"></span> &rarr; <span x-text="activity.new_value || '—'"></span></span>
                                                                </template>
                                                            </span>
                                                        </template>
                                                        <span class="block text-gray-400 mt-0.5" x-text="activity.created_at" :title="activity.created_at_full"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="(cardPanel.data.activities || []).length > 5 && !showAll">
                                                <button @click="showAll = true" class="text-xs text-primary-600 hover:text-primary-700">
                                                    Показати все (<span x-text="cardPanel.data.activities.length"></span>)
                                                </button>
                                            </template>
                                            <template x-if="!cardPanel.data.activities || cardPanel.data.activities.length === 0">
                                                <p class="text-center text-gray-400 text-xs py-2">Немає активності</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sidebar -->
                                <div class="hidden lg:block w-72 flex-shrink-0 px-4 py-4 space-y-4 bg-gray-50 dark:bg-gray-800/50">
                                    <div class="space-y-3">
                                        <!-- Status -->
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                Статус
                                            </label>
                                            <select x-model="cardPanel.data.card.column_id" @change="saveCardField('column_id', cardPanel.data.card.column_id)"
                                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white">
                                                <template x-for="col in cardPanel.data.columns" :key="col.id">
                                                    <option :value="col.id" x-text="col.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Epic -->
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                Проєкт
                                            </label>
                                            <select x-model="cardPanel.data.card.epic_id" @change="saveCardField('epic_id', cardPanel.data.card.epic_id)"
                                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm dark:text-white">
                                                <option value="">Без проєкту</option>
                                                <template x-for="epic in epics" :key="epic.id">
                                                    <option :value="epic.id" x-text="epic.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Assignee -->
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            dropdownStyle: {},
                                            updatePosition() {
                                                const btn = this.$el.querySelector('button');
                                                if (!btn) return;
                                                const rect = btn.getBoundingClientRect();
                                                this.dropdownStyle = {
                                                    left: rect.left + 'px',
                                                    width: rect.width + 'px',
                                                    top: (rect.bottom + 4) + 'px'
                                                };
                                            },
                                            get filtered() {
                                                if (!this.search) return cardPanel.data.people || [];
                                                return (cardPanel.data.people || []).filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                                            },
                                            get selectedPerson() {
                                                return (cardPanel.data.people || []).find(p => p.id == cardPanel.data.card.assigned_to);
                                            },
                                            select(id) {
                                                cardPanel.data.card.assigned_to = id;
                                                saveCardField('assigned_to', id);
                                                this.open = false;
                                                this.search = '';
                                            }
                                        }">
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Відповідальний
                                            </label>
                                            <div class="relative">
                                                <button type="button" @click="updatePosition(); open = !open; $nextTick(() => open && $refs.panelSearchInput.focus())"
                                                        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-left flex items-center justify-between dark:text-white">
                                                    <span x-show="!selectedPerson" class="text-gray-500">Не призначено</span>
                                                    <template x-if="selectedPerson">
                                                        <span class="flex items-center gap-2">
                                                            <span class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="selectedPerson.name.charAt(0)"></span>
                                                            <span x-text="selectedPerson.name" class="truncate"></span>
                                                        </span>
                                                    </template>
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                <div x-show="open" @click.away="open = false" x-transition
                                                     :style="dropdownStyle"
                                                     class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden">
                                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700">
                                                        <input type="text" x-model="search" x-ref="panelSearchInput"
                                                               placeholder="Пошук..."
                                                               class="w-full px-2 py-1.5 bg-gray-50 dark:bg-gray-700 border-0 rounded text-sm dark:text-white focus:ring-1 focus:ring-primary-500">
                                                    </div>
                                                    <div class="max-h-40 overflow-y-auto">
                                                        <button type="button" @click="select('')"
                                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2">
                                                            <span class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs">-</span>
                                                            <span class="text-gray-500 dark:text-gray-400">Не призначено</span>
                                                        </button>
                                                        <template x-for="person in filtered" :key="person.id">
                                                            <button type="button" @click="select(person.id)"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white flex items-center gap-2"
                                                                    :class="cardPanel.data.card.assigned_to == person.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                                                                <span class="w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-xs text-primary-600 dark:text-primary-400" x-text="person.name.charAt(0)"></span>
                                                                <span x-text="person.name" class="truncate"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Priority -->
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                                Пріоритет
                                            </label>
                                            <div class="flex gap-1">
                                                <button @click="cardPanel.data.card.priority = 'low'; saveCardField('priority', 'low')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'low' ? 'bg-gray-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Низ.
                                                </button>
                                                <button @click="cardPanel.data.card.priority = 'medium'; saveCardField('priority', 'medium')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'medium' ? 'bg-yellow-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Сер.
                                                </button>
                                                <button @click="cardPanel.data.card.priority = 'high'; saveCardField('priority', 'high')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'high' ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Вис.
                                                </button>
                                                <button @click="cardPanel.data.card.priority = 'urgent'; saveCardField('priority', 'urgent')"
                                                        class="flex-1 px-2 py-1.5 text-xs rounded-lg transition-colors"
                                                        :class="cardPanel.data.card.priority === 'urgent' ? 'bg-red-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'">
                                                    Терм.
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Due Date -->
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Дедлайн
                                            </label>
                                            <input type="date" x-model="cardPanel.data.card.due_date" @change="saveCardField('due_date', cardPanel.data.card.due_date)"
                                                   class="w-full px-3 py-2 bg-white dark:bg-gray-800 border rounded-lg text-sm dark:text-white"
                                                   :class="{
                                                       'border-red-300 dark:border-red-600': cardPanel.data.card.due_date && new Date(cardPanel.data.card.due_date + 'T23:59:59') < new Date() && !cardPanel.data.card.is_completed,
                                                       'border-gray-200 dark:border-gray-700': !cardPanel.data.card.due_date || cardPanel.data.card.is_completed
                                                   }">
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-1">
                                        <button @click="duplicateCard()" class="w-full px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            Дублювати
                                        </button>
                                        <button @click="deleteCard(cardPanel.data.card.id)" class="w-full px-3 py-2 text-sm text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Видалити
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
