{{-- CTA Section --}}
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-3xl p-8 md:p-12 text-center text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            <div class="relative">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Приєднуйтесь до нас</h2>
                <p class="text-xl text-white/80 mb-8 max-w-2xl mx-auto">
                    Будемо раді бачити вас на наших богослужіннях та заходах
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('public.contact', $church->slug) }}"
                       class="px-8 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors shadow-lg">
                        Зв'язатися з нами
                    </a>
                    <a href="{{ route('public.events', $church->slug) }}"
                       class="px-8 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-400 transition-colors border-2 border-white/20">
                        Переглянути події
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
