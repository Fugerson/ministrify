@extends('layouts.app')

@section('title', __('app.my_profile'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.my_profile') }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('my-schedule') }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                {{ __('app.my_schedule') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <form action="{{ route('my-profile.update') }}" method="POST" class="space-y-4"
                      x-data="{
                          photoUrl: {{ $person->photo ? "'" . Storage::url($person->photo) . "'" : 'null' }},
                          uploading: false,
                          async uploadPhoto(event) {
                              const file = event.target.files[0];
                              if (!file) return;
                              this.uploading = true;
                              const formData = new FormData();
                              formData.append('photo', file);
                              try {
                                  const res = await fetch('{{ route('my-profile.photo') }}', {
                                      method: 'POST',
                                      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                      body: formData
                                  });
                                  const data = await res.json();
                                  if (res.ok) this.photoUrl = data.photo_url;
                              } catch (e) { console.error(e); }
                              this.uploading = false;
                              event.target.value = '';
                          },
                          async removePhoto() {
                              this.uploading = true;
                              try {
                                  const res = await fetch('{{ route('my-profile.photo') }}', {
                                      method: 'POST',
                                      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                      body: JSON.stringify({ remove: '1' })
                                  });
                                  if (res.ok) this.photoUrl = null;
                              } catch (e) { console.error(e); }
                              this.uploading = false;
                          }
                      }">
                    @csrf
                    @method('PUT')
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,.heic,.heif" class="hidden" x-ref="photoInput" @change="uploadPhoto($event)">

                    @if($errors->any())
                        <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <ul class="text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center space-x-4 mb-6">
                        <!-- Editable avatar -->
                        <div class="relative flex-shrink-0 cursor-pointer" @click="$refs.photoInput.click()">
                            <template x-if="photoUrl">
                                <img :src="photoUrl" class="w-20 h-20 rounded-full object-cover">
                            </template>
                            <template x-if="!photoUrl">
                                <div class="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-2xl text-gray-500 dark:text-gray-400">{{ mb_substr($person->first_name, 0, 1) }}{{ mb_substr($person->last_name, 0, 1) }}</span>
                                </div>
                            </template>
                            <!-- Upload overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 hover:opacity-100 rounded-full transition-opacity">
                                <template x-if="!uploading">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </template>
                            </div>
                            <!-- Loading spinner -->
                            <div x-show="uploading" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full">
                                <svg class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <!-- Remove photo button -->
                            <button type="button" x-show="photoUrl && !uploading" @click.stop="removePhoto()"
                                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors z-10">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $person->full_name }}</h2>
                            <p class="text-gray-500 dark:text-gray-400">{{ auth()->user()->role === 'admin' ? __('app.role_admin') : (auth()->user()->role === 'leader' ? __('app.role_leader') : __('app.role_volunteer')) }}</p>
                            <button type="button" @click="$refs.photoInput.click()"
                                    class="inline-flex items-center gap-1.5 mt-1 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('app.change_photo') }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.phone') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $person->phone) }}"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.email') }}</label>
                            <input type="email" name="email" value="{{ old('email', $person->email) }}"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telegram</label>
                            <input type="text" name="telegram_username" value="{{ old('telegram_username', $person->telegram_username) }}"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('app.address') }}</label>
                            <input type="text" name="address" value="{{ old('address', $person->address) }}"
                                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors">
                            {{ __('app.save') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Telegram connection -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                 x-data="{
                     loading: false,
                     code: null,
                     botUsername: null,
                     error: null,
                     copied: false,
                     async generateCode() {
                         this.loading = true;
                         this.error = null;
                         try {
                             const response = await fetch('{{ route('my-profile.telegram.generate') }}', {
                                 method: 'POST',
                                 headers: {
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                     'Accept': 'application/json',
                                 }
                             });
                             const data = await response.json().catch(() => ({}));
                             if (data.error) {
                                 this.error = data.error;
                             } else {
                                 this.code = data.code;
                                 this.botUsername = data.bot_username;
                             }
                         } catch (e) {
                             this.error = '{{ __('app.connection_error') }}';
                         }
                         this.loading = false;
                     },
                     copyCode() {
                         navigator.clipboard.writeText(this.code);
                         this.copied = true;
                         setTimeout(() => this.copied = false, 2000);
                     }
                 }">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.telegram_bot') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.telegram_event_notifications') }}</p>
                    </div>
                </div>

                @if($person->telegram_chat_id)
                    <!-- Connected state -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-green-800 dark:text-green-200">{{ __('app.telegram_connected') }}</p>
                                <p class="text-sm text-green-600 dark:text-green-400">{{ __('app.you_will_receive_notifications') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <p class="mb-2"><strong>{{ __('app.available_commands') }}:</strong></p>
                        <ul class="space-y-1">
                            <li><code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/schedule</code> — {{ __('app.your_schedule_cmd') }}</li>
                            <li><code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/next</code> — {{ __('app.next_event_cmd') }}</li>
                            <li><code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">/help</code> — {{ __('app.help_cmd') }}</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('my-profile.telegram.unlink') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('{{ __('app.confirm_disconnect_telegram') }}')"
                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                            {{ __('app.disconnect_telegram') }}
                        </button>
                    </form>
                @else
                    <!-- Not connected state -->
                    <div class="space-y-4">
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ __('app.connect_telegram_desc') }}
                        </p>

                        <!-- Error message -->
                        <div x-show="error" x-cloak class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                            <p class="text-red-700 dark:text-red-400" x-text="error"></p>
                        </div>

                        <!-- Code display -->
                        <div x-show="code" x-cloak class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {{ __('app.connection_code_valid') }}
                            </p>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg px-4 py-3 font-mono text-2xl font-bold text-center tracking-widest text-primary-600 dark:text-primary-400 border border-gray-200 dark:border-gray-700"
                                     x-text="code"></div>
                                <button @click="copyCode()" type="button"
                                        class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        :class="{ 'text-green-600': copied }">
                                    <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <p><strong>{{ __('app.instruction') }}:</strong></p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>
                                        {{ __('app.open_bot') }}
                                        <a x-show="botUsername" :href="'https://t.me/' + botUsername" target="_blank"
                                           class="text-primary-600 dark:text-primary-400 hover:underline">
                                            @<span x-text="botUsername"></span>
                                        </a>
                                    </li>
                                    <li>{{ __('app.press_start') }}</li>
                                    <li>{{ __('app.enter_code') }} <span class="font-mono font-bold" x-text="code"></span></li>
                                </ol>
                            </div>
                        </div>

                        <!-- Generate button -->
                        <button @click="generateCode()" :disabled="loading" type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white font-medium rounded-xl transition-colors">
                            <svg x-show="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg x-show="!loading" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                            </svg>
                            <span x-text="code ? '{{ __('app.get_new_code') }}' : '{{ __('app.connect_telegram_btn') }}'"></span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Push Notifications -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                 x-data="{
                     supported: 'Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window,
                     permission: Notification.permission || 'default',
                     subscribed: false,
                     loading: false,
                     error: null,
                     async checkSubscription() {
                         if (!this.supported) return;
                         try {
                             const registration = await navigator.serviceWorker.ready;
                             const subscription = await registration.pushManager.getSubscription();
                             this.subscribed = !!subscription;
                         } catch (e) {
                             console.error('Check subscription error:', e);
                         }
                     },
                     async subscribe() {
                         this.loading = true;
                         this.error = null;
                         try {
                             // Get VAPID key
                             const keyResponse = await fetch('/api/push/public-key');
                             const { publicKey } = await keyResponse.json();
                             if (!publicKey) {
                                 this.error = '{{ __('app.push_not_configured') }}';
                                 return;
                             }

                             // Request permission
                             const permission = await Notification.requestPermission();
                             this.permission = permission;
                             if (permission !== 'granted') {
                                 this.error = '{{ __('app.notification_denied') }}';
                                 return;
                             }

                             // Subscribe
                             const registration = await navigator.serviceWorker.ready;
                             const subscription = await registration.pushManager.subscribe({
                                 userVisibleOnly: true,
                                 applicationServerKey: this.urlBase64ToUint8Array(publicKey)
                             });

                             // Send to server
                             const response = await fetch('/api/push/subscribe', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                     'Accept': 'application/json'
                                 },
                                 body: JSON.stringify(subscription.toJSON())
                             });

                             if (response.ok) {
                                 this.subscribed = true;
                             } else {
                                 const data = await response.json().catch(() => ({}));
                                 this.error = data.error || '{{ __('app.subscription_error') }}';
                             }
                         } catch (e) {
                             console.error('Subscribe error:', e);
                             this.error = '{{ __('app.error_prefix') }}' + e.message;
                         } finally {
                             this.loading = false;
                         }
                     },
                     async unsubscribe() {
                         this.loading = true;
                         try {
                             const registration = await navigator.serviceWorker.ready;
                             const subscription = await registration.pushManager.getSubscription();
                             if (subscription) {
                                 await subscription.unsubscribe();
                                 await fetch('/api/push/unsubscribe', {
                                     method: 'DELETE',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                     },
                                     body: JSON.stringify({ endpoint: subscription.endpoint })
                                 });
                             }
                             this.subscribed = false;
                         } catch (e) {
                             console.error('Unsubscribe error:', e);
                         } finally {
                             this.loading = false;
                         }
                     },
                     urlBase64ToUint8Array(base64String) {
                         const padding = '='.repeat((4 - base64String.length % 4) % 4);
                         const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                         const rawData = window.atob(base64);
                         const outputArray = new Uint8Array(rawData.length);
                         for (let i = 0; i < rawData.length; ++i) {
                             outputArray[i] = rawData.charCodeAt(i);
                         }
                         return outputArray;
                     }
                 }"
                 x-init="checkSubscription()">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.push_notifications_title') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.instant_browser_notifications') }}</p>
                    </div>
                </div>

                <template x-if="!supported">
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                        <p class="text-yellow-700 dark:text-yellow-400 text-sm">
                            {{ __('app.browser_not_supported_push') }}
                        </p>
                    </div>
                </template>

                <template x-if="supported">
                    <div>
                        <div x-show="error" x-cloak class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-4">
                            <p class="text-red-700 dark:text-red-400 text-sm" x-text="error"></p>
                        </div>

                        <template x-if="permission === 'denied'">
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                                <p class="text-red-700 dark:text-red-400 text-sm">
                                    {{ __('app.notifications_blocked') }}
                                </p>
                            </div>
                        </template>

                        <template x-if="permission !== 'denied'">
                            <div>
                                <template x-if="subscribed">
                                    <div>
                                        <div class="flex items-center gap-2 text-green-600 dark:text-green-400 mb-4">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-sm font-medium">{{ __('app.push_enabled') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            {{ __('app.push_enabled_desc') }}
                                        </p>
                                        <button @click="unsubscribe()" :disabled="loading" type="button"
                                                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                            <span x-show="!loading">{{ __('app.disable_notifications') }}</span>
                                            <span x-show="loading">{{ __('app.disabling_notifications') }}</span>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="!subscribed">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            {{ __('app.enable_push_desc') }}
                                        </p>
                                        <button @click="subscribe()" :disabled="loading" type="button"
                                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-purple-400 text-white font-medium rounded-xl transition-colors">
                                            <svg x-show="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                            <span x-text="loading ? '{{ __('app.enabling_notifications') }}' : '{{ __('app.enable_push_btn') }}'"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- PWA Install -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6"
                 x-data="{
                     installable: !!window.pwaInstallPrompt,
                     installed: false,
                     isIos: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
                     isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
                     showIosGuide: false,
                     init() {
                         window.addEventListener('pwa-installable', () => { this.installable = true; });
                         window.addEventListener('appinstalled', () => { this.installed = true; this.installable = false; });
                     },
                     async install() {
                         if (!window.pwaInstallPrompt) return;
                         window.pwaInstallPrompt.prompt();
                         const { outcome } = await window.pwaInstallPrompt.userChoice;
                         if (outcome === 'accepted') { this.installed = true; }
                         window.pwaInstallPrompt = null;
                         this.installable = false;
                     }
                 }">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.install_app') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.install_app_desc') }}</p>
                    </div>
                </div>

                <!-- Already installed / standalone mode -->
                <template x-if="isStandalone || installed">
                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm font-medium">{{ __('app.app_installed') }}</span>
                    </div>
                </template>

                <!-- Installable (Chrome/Edge/etc) -->
                <template x-if="!isStandalone && !installed && installable">
                    <button @click="install()" type="button"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('app.install_app_btn') }}
                    </button>
                </template>

                <!-- iOS instructions -->
                <template x-if="!isStandalone && !installed && !installable && isIos">
                    <div>
                        <button @click="showIosGuide = !showIosGuide" type="button"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('app.how_to_install') }}
                        </button>
                        <div x-show="showIosGuide" x-cloak x-transition class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li>{{ __('app.ios_step_1') }}</li>
                                <li>{{ __('app.ios_step_2') }}</li>
                                <li>{{ __('app.ios_step_3') }}</li>
                            </ol>
                        </div>
                    </div>
                </template>

                <!-- Not installable (desktop browser without PWA support) -->
                <template x-if="!isStandalone && !installed && !installable && !isIos">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('app.install_not_available') }}
                    </p>
                </template>
            </div>

            <!-- Menu Position -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
                 x-data="{
                     currentPosition: '{{ auth()->user()->settings['menu_position'] ?? ($currentChurch->menu_position ?? 'left') }}',
                     saving: false,
                     async setPosition(position) {
                         this.saving = true;
                         try {
                             const response = await fetch('{{ route('my-profile.menu-position') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                     'Accept': 'application/json'
                                 },
                                 body: JSON.stringify({ menu_position: position })
                             });
                             if (response.ok) {
                                 this.currentPosition = position;
                                 setTimeout(() => window.location.reload(), 500);
                             }
                         } catch (e) {
                             console.error('Menu position update error:', e);
                         }
                         this.saving = false;
                     }
                 }">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.menu_position') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('app.menu_position_desc') }}</p>
                </div>
                <div class="p-6">
                    @php
                        $menuPositions = [
                            ['id' => 'left', 'name' => __('app.position_left'), 'desc' => __('app.classic_sidebar'), 'icon' => '<svg class="w-full h-full" viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="5" width="20" height="50" rx="2" class="fill-primary-500"/><rect x="30" y="5" width="65" height="50" rx="2" class="fill-gray-200 dark:fill-gray-700"/></svg>'],
                            ['id' => 'right', 'name' => __('app.position_right'), 'desc' => __('app.right_menu'), 'icon' => '<svg class="w-full h-full" viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="5" width="65" height="50" rx="2" class="fill-gray-200 dark:fill-gray-700"/><rect x="75" y="5" width="20" height="50" rx="2" class="fill-primary-500"/></svg>'],
                            ['id' => 'top', 'name' => __('app.position_top'), 'desc' => __('app.horizontal_menu'), 'icon' => '<svg class="w-full h-full" viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="5" width="90" height="12" rx="2" class="fill-primary-500"/><rect x="5" y="22" width="90" height="33" rx="2" class="fill-gray-200 dark:fill-gray-700"/></svg>'],
                            ['id' => 'bottom', 'name' => __('app.position_bottom'), 'desc' => __('app.mobile_style'), 'icon' => '<svg class="w-full h-full" viewBox="0 0 100 60" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="5" width="90" height="38" rx="2" class="fill-gray-200 dark:fill-gray-700"/><rect x="5" y="48" width="90" height="10" rx="2" class="fill-primary-500"/></svg>'],
                        ];
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($menuPositions as $position)
                            <button type="button" @click="setPosition('{{ $position['id'] }}')" :disabled="saving"
                                    class="w-full p-4 rounded-xl border-2 transition-all hover:scale-[1.02]"
                                    :class="currentPosition === '{{ $position['id'] }}' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                                <div class="h-16 mb-3">
                                    {!! $position['icon'] !!}
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $position['name'] }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $position['desc'] }}</p>
                                <span x-show="currentPosition === '{{ $position['id'] }}'" x-cloak class="inline-block mt-2 text-xs bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full">{{ __('app.active') }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Theme Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700"
                 x-data="{
                     currentTheme: '{{ auth()->user()->settings['design_theme'] ?? '' }}',
                     saving: false,
                     async setTheme(theme) {
                         this.saving = true;
                         try {
                             const response = await fetch('{{ route('my-profile.theme') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                     'Accept': 'application/json'
                                 },
                                 body: JSON.stringify({ design_theme: theme })
                             });
                             if (response.ok) {
                                 this.currentTheme = theme;
                                 setTimeout(() => window.location.reload(), 500);
                             }
                         } catch (e) {
                             console.error('Theme update error:', e);
                         }
                         this.saving = false;
                     }
                 }">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.theme_design') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('app.personalize_appearance') }}</p>
                </div>

                <div class="p-6">
                    @php
                        $themes = [
                            ['id' => '', 'name' => __('app.theme_classic'), 'desc' => __('app.theme_classic_desc'), 'colors' => ['#fef7f0', '#fdf2f8', '#fef3c7']],
                            ['id' => 'modern', 'name' => __('app.theme_morning'), 'desc' => __('app.theme_morning_desc'), 'colors' => ['#fce7f3', '#fed7aa', '#fef3c7']],
                            ['id' => 'glass', 'name' => __('app.theme_evening'), 'desc' => __('app.theme_evening_desc'), 'colors' => ['#1e1b4b', '#172554', '#fbbf24']],
                            ['id' => 'corporate', 'name' => __('app.theme_nature'), 'desc' => __('app.theme_nature_desc'), 'colors' => ['#ecfdf5', '#d1fae5', '#10b981']],
                            ['id' => 'ocean', 'name' => __('app.theme_ocean'), 'desc' => __('app.theme_ocean_desc'), 'colors' => ['#ecfeff', '#e0f2fe', '#06b6d4']],
                            ['id' => 'sunset', 'name' => __('app.theme_sunset'), 'desc' => __('app.theme_sunset_desc'), 'colors' => ['#fce7f3', '#f3e8ff', '#a855f7']],
                        ];
                    @endphp

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($themes as $theme)
                        <button type="button" @click="setTheme('{{ $theme['id'] }}')" :disabled="saving"
                                class="relative p-4 rounded-xl border-2 transition-all text-left hover:scale-[1.02]"
                                :class="currentTheme === '{{ $theme['id'] }}' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                            <div class="flex gap-1.5 mb-2">
                                @foreach($theme['colors'] as $color)
                                <div class="w-6 h-6 rounded-full shadow-sm border border-gray-200 dark:border-gray-600" style="background-color: {{ $color }}"></div>
                                @endforeach
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $theme['name'] }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $theme['desc'] }}</p>
                            <span x-show="currentTheme === '{{ $theme['id'] }}'" x-cloak class="inline-block mt-2 text-xs bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full">{{ __('app.active') }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Upcoming assignments -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('app.my_events') }}</h3>
                @if($upcomingAssignments->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">{{ __('app.no_planned_events') }}</p>
                @else
                <div class="space-y-3">
                    @foreach($upcomingAssignments->take(5) as $assignment)
                    @if($assignment->event)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->event->date->format('d.m') }} - {{ $assignment->event->ministry?->name ?? $assignment->event->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->position?->name }}</p>
                        </div>
                        <span class="text-lg">
                            @if($assignment->status === 'confirmed') ✅
                            @elseif($assignment->status === 'declined') ❌
                            @else ⏳
                            @endif
                        </span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Blockout Dates -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.unavailability_periods') }}</h3>
                    </div>
                    <a href="{{ route('blockouts.index') }}" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        {{ __('app.manage') }}
                    </a>
                </div>

                @php
                    $activeBlockouts = $person->blockoutDates()->active()->upcoming()->take(3)->get();
                @endphp

                @if($activeBlockouts->isNotEmpty())
                <div class="space-y-2 mb-4">
                    @foreach($activeBlockouts as $blockout)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <span class="text-sm">
                                @switch($blockout->reason)
                                    @case('vacation') 🏖️ @break
                                    @case('travel') ✈️ @break
                                    @case('sick') 🏥 @break
                                    @case('family') 👨‍👩‍👧 @break
                                    @case('work') 💼 @break
                                    @default 📅
                                @endswitch
                            </span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $blockout->date_range }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $blockout->reason_label }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('app.no_active_unavailability') }}</p>
                @endif

                <a href="{{ route('blockouts.create') }}"
                   class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.add_period') }}
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
