@extends('layouts.app')

@section('title', __('app.msg_new_message'))

@section('content')
<div class="max-w-2xl" x-data="messageForm()" x-effect="recipientType; $nextTick(() => loadPreview())">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form @submit.prevent="confirmAndSubmit($refs.msgForm)" x-ref="msgForm" class="space-y-6">

            <!-- Recipient Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('app.msg_recipients') }}</label>

                <!-- Basic filters -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-2">
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'all' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="all" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'all' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">{{ __('app.msg_all') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'tag' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="tag" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'tag' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">{{ __('app.msg_by_tag') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'ministry' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="ministry" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'ministry' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">{{ __('app.msg_team') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'group' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="group" x-model="recipientType" class="sr-only">
                        <span class="text-sm font-medium" :class="recipientType === 'group' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-300'">{{ __('app.msg_group') }}</span>
                    </label>
                </div>

                <!-- Advanced filters -->
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <label class="relative flex items-center justify-center px-3 py-2.5 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'gender' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="gender" x-model="recipientType" class="sr-only">
                        <span class="text-xs font-medium" :class="recipientType === 'gender' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400'">{{ __('app.msg_gender') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-3 py-2.5 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'role' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="role" x-model="recipientType" class="sr-only">
                        <span class="text-xs font-medium" :class="recipientType === 'role' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400'">{{ __('app.msg_role') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-3 py-2.5 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'birthday' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="birthday" x-model="recipientType" class="sr-only">
                        <span class="text-xs font-medium" :class="recipientType === 'birthday' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400'">{{ __('app.msg_birthday_people') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-3 py-2.5 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'membership' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="membership" x-model="recipientType" class="sr-only">
                        <span class="text-xs font-medium" :class="recipientType === 'membership' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400'">{{ __('app.msg_status') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-3 py-2.5 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'age' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="age" x-model="recipientType" class="sr-only">
                        <span class="text-xs font-medium" :class="recipientType === 'age' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400'">{{ __('app.msg_age') }}</span>
                    </label>
                    <label class="relative flex items-center justify-center px-3 py-2.5 border rounded-xl cursor-pointer transition-colors"
                           :class="recipientType === 'new_members' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                        <input type="radio" name="recipient_type" value="new_members" x-model="recipientType" class="sr-only">
                        <span class="text-xs font-medium" :class="recipientType === 'new_members' ? 'text-primary-700 dark:text-primary-300' : 'text-gray-600 dark:text-gray-400'">{{ __('app.msg_new_people') }}</span>
                    </label>
                </div>
            </div>

            <!-- Tag Select -->
            <div x-show="recipientType === 'tag'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_select_tag') }}</label>
                <select name="tag_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('app.msg_select_option') }}</option>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Ministry Select -->
            <div x-show="recipientType === 'ministry'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_select_team') }}</label>
                <select name="ministry_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('app.msg_select_option') }}</option>
                    @foreach($ministries as $ministry)
                    <option value="{{ $ministry->id }}">{{ $ministry->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Group Select -->
            <div x-show="recipientType === 'group'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_select_group') }}</label>
                <select name="group_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('app.msg_select_option') }}</option>
                    @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Gender Select -->
            <div x-show="recipientType === 'gender'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_select_gender') }}</label>
                <select name="gender" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('app.msg_select_option') }}</option>
                    <option value="male">{{ __('app.msg_male') }}</option>
                    <option value="female">{{ __('app.msg_female') }}</option>
                </select>
            </div>

            <!-- Role Select -->
            <div x-show="recipientType === 'role'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_select_role') }}</label>
                <select name="church_role_id" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('app.msg_select_option') }}</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Birthday info -->
            <div x-show="recipientType === 'birthday'" x-cloak>
                <div class="bg-amber-50 dark:bg-amber-900/30 rounded-xl p-4">
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        {{ __('app.msg_birthday_info', ['month' => \Carbon\Carbon::now()->translatedFormat('F')]) }}
                    </p>
                </div>
            </div>

            <!-- Membership Status Select -->
            <div x-show="recipientType === 'membership'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_membership_status') }}</label>
                <select name="membership_status" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('forms.select_option') }}</option>
                    <option value="guest">{{ __('app.guest') }}</option>
                    <option value="newcomer">{{ __('app.newcomer') }}</option>
                    <option value="member">{{ __('app.church_member') }}</option>
                    <option value="servant">{{ __('app.servant') }}</option>
                    <option value="leader">{{ __('app.leader') }}</option>
                    <option value="leadership">{{ __('app.leadership') }}</option>
                </select>
            </div>

            <!-- Age Group Select -->
            <div x-show="recipientType === 'age'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_age_group') }}</label>
                <select name="age_group" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('forms.select_option') }}</option>
                    <option value="child">{{ \App\Models\Person::AGE_CATEGORIES['child']['label'] }} (0-12)</option>
                    <option value="teen">{{ \App\Models\Person::AGE_CATEGORIES['teen']['label'] }} (13-17)</option>
                    <option value="youth">{{ \App\Models\Person::AGE_CATEGORIES['youth']['label'] }} (18-35)</option>
                    <option value="adults">{{ \App\Models\Person::AGE_CATEGORIES['adult']['label'] }} (36-59)</option>
                    <option value="seniors">{{ \App\Models\Person::AGE_CATEGORIES['senior']['label'] }} (60+)</option>
                </select>
            </div>

            <!-- New Members info -->
            <div x-show="recipientType === 'new_members'" x-cloak>
                <div class="bg-green-50 dark:bg-green-900/30 rounded-xl p-4">
                    <p class="text-sm text-green-700 dark:text-green-300">
                        {{ __('app.msg_new_members_info') }}
                    </p>
                </div>
            </div>

            <!-- Template Select -->
            @if($templates->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_template_optional') }}</label>
                <select @change="if($event.target.value) message = templates[$event.target.value]"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white">
                    <option value="">{{ __('app.msg_no_template') }}</option>
                    @foreach($templates as $template)
                    <option value="{{ $loop->index }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Message -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.msg_message_text') }}</label>
                <textarea name="message" rows="6" required x-model="message"
                          placeholder="{{ __('app.msg_message_placeholder') }}"
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl dark:text-white"></textarea>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.msg_template_variables') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="message.length + '/4000'"></p>
                </div>
            </div>

            <!-- Recipient Preview -->
            <div x-show="previewData" class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div class="ml-3 text-sm">
                        <p class="text-blue-700 dark:text-blue-300">
                            {{ __('app.msg_recipients_count') }} <strong x-text="previewData?.total || 0"></strong>,
                            {{ __('app.msg_with_telegram') }} <strong x-text="previewData?.with_telegram || 0"></strong>
                            <template x-if="previewData?.without_telegram > 0">
                                <span class="text-amber-600 dark:text-amber-400">({{ __('app.msg_without_telegram') }} <span x-text="previewData.without_telegram"></span>)</span>
                            </template>
                        </p>
                    </div>
                </div>
            </div>

            <div x-show="!previewData" class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            {{ __('app.msg_telegram_only_info') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('messages.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-center text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium">
                    {{ __('app.msg_cancel') }}
                </a>
                <button type="submit"
                        :disabled="saving"
                        :class="saving ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary-600 hover:bg-primary-700'"
                        class="px-5 py-2.5 text-white rounded-xl font-medium transition-colors inline-flex items-center gap-2">
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? @json(__('app.msg_sending')) : @json(__('app.msg_send'))"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
var _msgI18n = {!! json_encode([
    'no_recipients' => __('app.msg_no_recipients_with_tg'),
    'confirm_send' => __('app.msg_confirm_send'),
]) !!};

function messageForm() {
    return {
        ...ajaxForm({ url: '{{ route('messages.send') }}', method: 'POST' }),
        recipientType: 'all',
        message: '',
        templates: @json($templates->pluck('content')),
        previewData: null,
        previewLoading: false,

        async loadPreview() {
            this.previewLoading = true;
            try {
                const formData = new FormData(this.$refs.msgForm);
                const response = await fetch('{{ route('messages.preview') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                this.previewData = await response.json();
            } catch (e) {
                this.previewData = null;
            }
            this.previewLoading = false;
        },

        async confirmAndSubmit(formEl) {
            if (!this.previewData) {
                await this.loadPreview();
            }
            const count = this.previewData?.with_telegram || 0;
            if (count === 0) {
                showToast('error', _msgI18n.no_recipients);
                return;
            }
            if (!confirm(_msgI18n.confirm_send.replace(':count', count))) {
                return;
            }
            this.submit(formEl);
        }
    }
}
</script>
@endsection
