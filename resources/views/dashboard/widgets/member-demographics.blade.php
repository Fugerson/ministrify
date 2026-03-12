{{-- Member Demographics Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:p-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/50 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('app.demographics_title') }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.members_with_birthdate', ['total' => $demographics['total'] ?? 0, 'with_birthdate' => $demographics['with_birthdate'] ?? 0]) }}</p>
        </div>
    </div>

    @if(!empty($demographics))
    {{-- Gender Split --}}
    <div class="mb-5">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">{{ __('app.gender_label') }}</p>
        @php
            $genderTotal = ($demographics['male'] ?? 0) + ($demographics['female'] ?? 0) + ($demographics['unknown_gender'] ?? 0);
            $malePercent = $genderTotal > 0 ? round($demographics['male'] / $genderTotal * 100) : 0;
            $femalePercent = $genderTotal > 0 ? round($demographics['female'] / $genderTotal * 100) : 0;
            $unknownPercent = $genderTotal > 0 ? (100 - $malePercent - $femalePercent) : 0;
        @endphp
        <div class="flex h-5 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
            @if($malePercent > 0)
                <div class="bg-blue-500 dark:bg-blue-400 transition-all duration-500" style="width: {{ $malePercent }}%"></div>
            @endif
            @if($femalePercent > 0)
                <div class="bg-pink-500 dark:bg-pink-400 transition-all duration-500" style="width: {{ $femalePercent }}%"></div>
            @endif
            @if($unknownPercent > 0)
                <div class="bg-gray-300 dark:bg-gray-500 transition-all duration-500" style="width: {{ $unknownPercent }}%"></div>
            @endif
        </div>
        <div class="flex items-center gap-4 mt-2 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 dark:bg-blue-400"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ __('app.male_count', ['count' => $demographics['male'] ?? 0, 'percent' => $malePercent]) }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500 dark:bg-pink-400"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ __('app.female_count', ['count' => $demographics['female'] ?? 0, 'percent' => $femalePercent]) }}</span>
            </div>
            @if(($demographics['unknown_gender'] ?? 0) > 0)
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-500"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ __('app.unknown_gender', ['count' => $demographics['unknown_gender'] ?? 0]) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Age Groups --}}
    <div>
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">{{ __('app.age_groups_label') }}</p>
        @php
            $ageGroups = $demographics['age_groups'] ?? [];
            $maxAge = count($ageGroups) > 0 ? max(array_values($ageGroups)) : 0;
            $ageColors = [
                'children' => ['bg' => 'bg-amber-400 dark:bg-amber-500', 'text' => 'text-amber-600 dark:text-amber-400', 'label' => __('app.children_age')],
                'teens' => ['bg' => 'bg-purple-400 dark:bg-purple-500', 'text' => 'text-purple-600 dark:text-purple-400', 'label' => __('app.teens_age')],
                'youth' => ['bg' => 'bg-blue-400 dark:bg-blue-500', 'text' => 'text-blue-600 dark:text-blue-400', 'label' => __('app.youth_age')],
                'adults' => ['bg' => 'bg-green-400 dark:bg-green-500', 'text' => 'text-green-600 dark:text-green-400', 'label' => __('app.adults_age')],
                'seniors' => ['bg' => 'bg-gray-400 dark:bg-gray-500', 'text' => 'text-gray-600 dark:text-gray-400', 'label' => __('app.seniors_age')],
                'unknown' => ['bg' => 'bg-gray-300 dark:bg-gray-600', 'text' => 'text-gray-500 dark:text-gray-500', 'label' => __('app.unknown_age')],
            ];
        @endphp
        <div class="space-y-3">
            @foreach($ageColors as $key => $color)
                @php
                    $count = $ageGroups[$key] ?? 0;
                    $percent = $maxAge > 0 ? round($count / $maxAge * 100) : 0;
                @endphp
                @if($count > 0 || $key !== 'unknown')
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium {{ $color['text'] }}">{{ $color['label'] }}</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $count }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                        <div class="h-full rounded-full {{ $color['bg'] }} transition-all duration-500" style="width: {{ $percent }}%; min-width: {{ $count > 0 ? '4px' : '0' }}"></div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @else
    <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">{{ __('app.no_demographic_data') }}</p>
    @endif
</div>
