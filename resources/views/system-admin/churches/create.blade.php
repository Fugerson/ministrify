@extends('layouts.system-admin')

@section('title', __('app.sa_add_church'))

@section('actions')
<a href="{{ route('system.churches.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-900 dark:text-white font-medium rounded-lg">
    ← {{ __('app.back') }}
</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('system.churches.store') }}" class="space-y-6">
        @csrf

        <!-- Church Info -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3">{{ __('app.sa_church_info') }}</h2>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">{{ __('app.church_name') }} *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       >
                @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">{{ __('app.city') }} *</label>
                <input type="text" name="city" value="{{ old('city') }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       >
                @error('city')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">{{ __('app.address') }}</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       >
                @error('address')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Admin User -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-3">{{ __('app.sa_church_admin') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.sa_admin_will_be_created') }}</p>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">{{ __('app.sa_admin_name') }} *</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       >
                @error('admin_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">{{ __('app.sa_admin_email') }} *</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       >
                @error('admin_email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">{{ __('app.sa_password') }} *</label>
                <input type="password" name="admin_password" required
                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       >
                @error('admin_password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('system.churches.index') }}"
               class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-xl">
                {{ __('app.cancel') }}
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">
                {{ __('app.sa_create_church') }}
            </button>
        </div>
    </form>
</div>
@endsection
