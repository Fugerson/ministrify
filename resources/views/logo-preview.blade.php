<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ministrify — 50 Logo Concepts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .ck { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16'%3E%3Crect width='8' height='8' fill='%23f0f0f0'/%3E%3Crect x='8' y='8' width='8' height='8' fill='%23f0f0f0'/%3E%3Crect x='8' width='8' height='8' fill='%23fff'/%3E%3Crect y='8' width='8' height='8' fill='%23fff'/%3E%3C/svg%3E"); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Ministrify — Logo Concepts</h1>
        <p class="text-gray-500 mb-6">50 вариантов с библейским контекстом. Каждый: полный логотип + иконка в разных размерах.</p>

        @php
            $logos = [
                ['v1-vine', 'Лоза і Гілки', 'Іван 15:5 — «Я виноградна лоза, а ви — гілки»', '#6366f1'],
                ['v2-body', 'Тіло Христове', '1 Кор 12:27 — «Ви — тіло Христове, і кожен з вас — його частина»', '#0ea5e9'],
                ['v3-seed', 'Гірчичне Зерно', 'Мт 13:31-32 — «Найменше зерно стає найбільшим деревом»', '#10b981'],
                ['v4-light', 'Світло Світу', 'Мт 5:14-16 — «Ви — світло світу. Не може сховатися місто на горі»', '#f59e0b'],
                ['v5-water', 'Жива Вода', 'Ів 7:38 — «Потечуть ріки живої води»', '#3b82f6'],
                ['v6-shepherd', 'Добрий Пастир', 'Ів 10:11, Пс 23 — «Я — добрий пастир»', '#f59e0b'],
                ['v7-fish', 'Іхтіс', 'Мт 4:19 — Символ ранніх християн', '#0284c7'],
                ['v8-crown', 'Вінець Слави', '2 Тим 4:8 — «Чекає мене вінець правди»', '#eab308'],
                ['v9-hands', 'Молитовні Руки', '1 Тим 2:8 — «Здіймайте чисті руки на молитву»', '#dc2626'],
                ['v10-dove', 'Голуб Миру', 'Бут 8:11 — Голуб з оливковою гілкою', '#0ea5e9'],
                ['v11-anchor', 'Якір Надії', 'Євр 6:19 — «Надія як якір для душі»', '#2563eb'],
                ['v12-bread', 'Хліб і Вино', '1 Кор 11:24 — «Це тіло Моє за вас»', '#b91c1c'],
                ['v13-rock', 'На Скелі', 'Мт 7:24-25 — «Побудував дім свій на скелі»', '#78716c'],
                ['v14-fire', 'Вогонь П\'ятидесятниці', 'Дії 2:3 — «Язики вогню»', '#ea580c'],
                ['v15-star', 'Зірка Віфлеєму', 'Мт 2:9 — «Зірка зупинилася над місцем»', '#ca8a04'],
                ['v16-olive', 'Оливкове Дерево', 'Рим 11:17 — «Прищеплені до оливи»', '#65a30d'],
                ['v17-heart', 'Бог є Любов', '1 Ів 4:8 — «Бог є любов»', '#e11d48'],
                ['v18-mountain', 'Гора Віри', 'Мт 17:20 — «Віра пересуває гори»', '#7c3aed'],
                ['v19-gate', 'Вузькі Ворота', 'Мт 7:13-14 — «Входьте вузькою брамою»', '#d97706'],
                ['v20-scroll', 'Книга Життя', 'Об 3:5 — «Книга життя»', '#92400e'],
                ['v21-rainbow', 'Веселка Заповіту', 'Бут 9:13 — «Мій знак заповіту»', '#3b82f6'],
                ['v22-temple', 'Божий Храм', '1 Кор 3:16 — «Ви — храм Божий»', '#475569'],
                ['v23-wheat', 'Жнива', 'Мт 9:37 — «Жнива великі, а робітників мало»', '#ca8a04'],
                ['v24-key', 'Ключ Давида', 'Об 3:7 — «Хто має ключ Давида»', '#a16207'],
                ['v25-bridge', 'Міст Миру', 'Еф 2:14 — «Він наш мир, що зробив із двох одне»', '#0d9488'],
                ['v26-shield', 'Щит Віри', 'Еф 6:16 — «Щит віри»', '#1d4ed8'],
                ['v27-bell', 'Дзвін Покликання', 'Іс 6:8 — «Ось я, пошли мене»', '#b45309'],
                ['v28-eye', 'Провидіння', 'Пр 15:3 — «Очі Господні скрізь»', '#0f766e'],
                ['v29-ladder', 'Сходи Якова', 'Бут 28:12 — «Сходи від землі до неба»', '#38bdf8'],
                ['v30-lily', 'Лілея Долини', 'Пісня 2:1 — «Я лілея долини»', '#059669'],
                ['v31-sun', 'Сонце Правди', 'Мал 4:2 — «Зійде сонце правди»', '#d97706'],
                ['v32-circle', 'Альфа і Омега', 'Об 22:13 — «Я Альфа і Омега»', '#7c3aed'],
                ['v33-path', 'Дорога', 'Ів 14:6 — «Я дорога, істина і життя»', '#059669'],
                ['v34-net', 'Ловці Людей', 'Мт 4:19 — «Зроблю вас ловцями людей»', '#06b6d4'],
                ['v35-tower', 'Міцна Вежа', 'Пр 18:10 — «Ім\'я Господнє — міцна вежа»', '#334155'],
                ['v36-wings', 'Крила Орла', 'Іс 40:31 — «Полетять як орли»', '#4f46e5'],
                ['v37-oil', 'Єлей Помазання', 'Пс 133:2 — «Єлей на голову»', '#d97706'],
                ['v38-cloud', 'Стовп Хмари', 'Вих 13:21 — «У хмарному стовпі»', '#3b82f6'],
                ['v39-pearl', 'Дорогоцінна Перлина', 'Мт 13:46 — «Знайшовши одну дорогоцінну перлину»', '#a8a29e'],
                ['v40-harp', 'Арфа Хвали', 'Пс 150 — «Хваліте Його на арфі»', '#ca8a04'],
                ['v41-compass', 'Компас', 'Пс 119:105 — «Твоє слово — світильник ногам моїм»', '#475569'],
                ['v42-tree', 'Дерево Життя', 'Об 22:2 — «Дерево життя»', '#22c55e'],
                ['v43-wave', 'Розділені Води', 'Вих 14 — «Води розступилися»', '#0891b2'],
                ['v44-candle', 'Світильник', 'Об 1:20 — «Сім золотих світильників»', '#f59e0b'],
                ['v45-chain', 'Розірвані Кайдани', 'Гал 5:1 — «Для свободи Христос визволив нас»', '#eab308'],
                ['v46-door', 'Двері', 'Ів 10:9 — «Я — двері»', '#fbbf24'],
                ['v47-horn', 'Сурма', '1 Сол 4:16 — «Засурмить Божа сурма»', '#ca8a04'],
                ['v48-cross-modern', 'Сучасний Хрест', 'Гал 6:14 — «Хвалитися хрестом Господа»', '#dc2626'],
                ['v49-mosaic', 'Мозаїка', '1 Кор 12:12 — «Багато частин — одне тіло»', '#3b82f6'],
                ['v50-infinity', 'Вічність', 'Ів 3:16 — «Мав життя вічне»', '#8b5cf6'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($logos as $i => $logo)
            @php [$file, $name, $verse, $color] = $logo; @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-400">{{ $i + 1 }}</span>
                        <h2 class="text-base font-bold text-gray-900">{{ $name }}</h2>
                    </div>
                    <p class="text-xs text-gray-500 italic mt-0.5">{{ $verse }}</p>
                </div>

                <div class="p-4 space-y-3">
                    {{-- Full logo --}}
                    <div class="flex gap-3">
                        <div class="ck rounded-lg p-3 flex-1 flex items-center justify-center" style="min-height:70px">
                            <img src="/logo-variants/{{ $file }}-full.svg" alt="{{ $name }}" class="max-w-full h-auto" style="max-height:60px"
                                 onerror="this.parentElement.innerHTML='<span class=\'text-xs text-gray-300\'>generating...</span>'">
                        </div>
                        <div class="bg-gray-900 rounded-lg p-3 flex-1 flex items-center justify-center" style="min-height:70px">
                            <img src="/logo-variants/{{ $file }}-full.svg" alt="{{ $name }}" class="max-w-full h-auto" style="max-height:60px"
                                 onerror="this.parentElement.innerHTML='<span class=\'text-xs text-gray-600\'>generating...</span>'">
                        </div>
                    </div>

                    {{-- Icons --}}
                    <div class="flex items-end gap-2 flex-wrap">
                        @foreach([64, 48, 32, 16] as $size)
                        <div class="text-center">
                            <div class="ck rounded p-1 inline-block">
                                <img src="/logo-variants/{{ $file }}-icon.svg" alt="" style="width:{{ $size }}px;height:{{ $size }}px"
                                     onerror="this.style.display='none'">
                            </div>
                            <p class="text-[10px] text-gray-400">{{ $size }}</p>
                        </div>
                        @endforeach
                        <div class="text-center">
                            <div class="bg-gray-900 rounded p-1 inline-block">
                                <img src="/logo-variants/{{ $file }}-icon.svg" alt="" style="width:48px;height:48px"
                                     onerror="this.style.display='none'">
                            </div>
                            <p class="text-[10px] text-gray-400">dark</p>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>