{{-- FAQ Section --}}
@if(isset($faqs) && $faqs->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Часті запитання</h2>
            <p class="text-gray-600 mt-2">Відповіді на популярні запитання</p>
        </div>

        <div class="space-y-4">
            @foreach($faqs as $faq)
                <details class="group bg-white rounded-xl shadow-sm border border-gray-100">
                    <summary class="flex items-center justify-between p-5 cursor-pointer list-none">
                        <h3 class="font-semibold text-gray-900 pr-4">{{ $faq->question }}</h3>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-5 pb-5 text-gray-600">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@else
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Часті запитання</h2>
            <p class="text-gray-600 mt-2">Відповіді на популярні запитання</p>
        </div>

        <div class="space-y-4">
            @foreach([
                ['q' => 'О котрій годині проходять богослужіння?', 'a' => 'Основне богослужіння проходить щонеділі о 10:00. Молитовна зустріч — щосереди о 18:30. Молодіжна зустріч — щоп\'ятниці о 19:00.'],
                ['q' => 'Чи є у вас дитяче служіння?', 'a' => 'Так! Під час недільного богослужіння працює дитяча програма для дітей від 3 до 12 років з біблійними уроками, іграми та творчістю.'],
                ['q' => 'Як я можу приєднатися до малої групи?', 'a' => 'Ви можете обрати малу групу на нашому сайті та залишити заявку, або звернутися до координатора після богослужіння.'],
                ['q' => 'Чи потрібно реєструватися для відвідування?', 'a' => 'Ні, реєстрація не потрібна. Наші двері відкриті для всіх! Просто приходьте — ми будемо раді вас бачити.'],
            ] as $faq)
                <details class="group bg-white rounded-xl shadow-sm border border-gray-100">
                    <summary class="flex items-center justify-between p-5 cursor-pointer list-none">
                        <h3 class="font-semibold text-gray-900 pr-4">{{ $faq['q'] }}</h3>
                        <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-5 pb-5 text-gray-600">
                        {{ $faq['a'] }}
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif
