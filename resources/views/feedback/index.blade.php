@extends('layouts.app')

@section('title', 'Відгуки')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="feedbackAdmin()">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Відгуки</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Анонімні відгуки з публічного сайту</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Всього</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Нових</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['new'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Середній рейтинг</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                {{ $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '—' }}
                @if($stats['avg_rating'])
                <span class="text-sm">/ 5</span>
                @endif
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Категорій</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($stats['by_category']) }}</p>
        </div>
    </div>

    <!-- Chart -->
    @if(count($stats['weekly']) > 1)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Тренд відгуків за 12 тижнів</h2>
        <canvas id="feedbackChart" height="80"></canvas>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="category" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Усі категорії</option>
                @foreach(\App\Models\Feedback::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Усі статуси</option>
                @foreach(\App\Models\Feedback::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="rating" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Усі рейтинги</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ str_repeat('★', $i) }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Фільтрувати
            </button>
            @if(request()->hasAny(['category', 'status', 'rating']))
                <a href="{{ route('feedback.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Скинути
                </a>
            @endif
        </form>
    </div>

    <!-- Feedback List -->
    <div class="space-y-4">
        @forelse($feedbacks as $feedback)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5"
             x-data="{ expanded: false }">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <!-- Category badge -->
                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full
                            {{ $feedback->category === 'complaint' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
                               ($feedback->category === 'suggestion' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                               ($feedback->category === 'worship' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' :
                               ($feedback->category === 'sermon' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' :
                               'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'))) }}">
                            {{ $feedback->category_label }}
                        </span>
                        <!-- Status badge -->
                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full cursor-pointer
                            {{ $feedback->status === 'new' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                               ($feedback->status === 'read' ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' :
                               'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400') }}"
                              @click="cycleStatus({{ $feedback->id }}, '{{ $feedback->status }}', $el)">
                            {{ $feedback->status_label }}
                        </span>
                        <!-- Rating -->
                        @if($feedback->rating)
                        <span class="text-yellow-500 text-sm">{{ $feedback->star_rating_display }}</span>
                        @endif
                        <!-- Time -->
                        <span class="text-xs text-gray-400">{{ $feedback->created_at->diffForHumans() }}</span>
                    </div>
                    <!-- Message -->
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $feedback->message }}</p>
                    <!-- Author info -->
                    @if(!$feedback->is_anonymous && $feedback->name)
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ $feedback->name }}
                        @if($feedback->email)
                            &middot; <a href="mailto:{{ $feedback->email }}" class="text-primary-600 hover:underline">{{ $feedback->email }}</a>
                        @endif
                    </p>
                    @endif
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button @click="expanded = !expanded" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Нотатки">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('feedback.destroy', $feedback) }}" onsubmit="return confirm('Видалити відгук?')">
                        @csrf @method('DELETE')
                        <button class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Видалити">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <!-- Admin notes (expandable) -->
            <div x-show="expanded" x-cloak class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Нотатки адміністратора</label>
                <textarea class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                          rows="2" placeholder="Додайте нотатку..."
                          @change="saveNotes({{ $feedback->id }}, $event.target.value)">{{ $feedback->admin_notes }}</textarea>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">Ще немає відгуків</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Відгуки з'являться тут після того, як відвідувачі публічного сайту залишать їх.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($feedbacks->hasPages())
    <div class="mt-4">
        {{ $feedbacks->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function feedbackAdmin() {
    return {
        async cycleStatus(id, current, el) {
            const next = current === 'new' ? 'read' : current === 'read' ? 'archived' : 'new';
            try {
                const res = await fetch(`/feedback/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: next })
                });
                if (res.ok) window.location.reload();
            } catch (e) {
                console.error(e);
            }
        },
        async saveNotes(id, notes) {
            try {
                await fetch(`/feedback/${id}/notes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ admin_notes: notes })
                });
            } catch (e) {
                console.error(e);
            }
        }
    };
}
</script>

@if(count($stats['weekly']) > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('feedbackChart');
    if (!ctx) return;
    const data = @json($stats['weekly']);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.week),
            datasets: [
                {
                    label: 'Середній рейтинг',
                    data: data.map(d => d.avg_rating ? parseFloat(d.avg_rating).toFixed(1) : null),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Кількість',
                    data: data.map(d => d.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                y: { type: 'linear', position: 'left', min: 0, max: 5, title: { display: true, text: 'Рейтинг' } },
                y1: { type: 'linear', position: 'right', min: 0, grid: { drawOnChartArea: false }, title: { display: true, text: 'Кількість' } },
            },
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
@endif
@endpush
@endsection
