@extends('layouts.landing')

@section('title', 'Account Deletion — Ministrify')
@section('description', 'How to request deletion of your Ministrify account and associated data')

@section('content')
<section class="pt-32 pb-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Account Deletion</h1>
            <p class="text-gray-600 dark:text-gray-400">Ministrify — Church Management Platform</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">

                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">How to Request Account Deletion</h2>

                <p>If you would like to delete your Ministrify account and all associated data, please follow these steps:</p>

                <ol class="space-y-3">
                    <li><strong>Step 1:</strong> Log in to your Ministrify account at <a href="https://ministrify.app/login" class="text-blue-600 dark:text-blue-400">ministrify.app/login</a></li>
                    <li><strong>Step 2:</strong> Go to <strong>Settings</strong> → <strong>My Profile</strong></li>
                    <li><strong>Step 3:</strong> Scroll to the bottom and click <strong>"Delete Account"</strong></li>
                    <li><strong>Step 4:</strong> Confirm the deletion when prompted</li>
                </ol>

                <p>Alternatively, you can send a deletion request to <a href="mailto:support@ministrify.app" class="text-blue-600 dark:text-blue-400">support@ministrify.app</a> from the email address associated with your account.</p>

                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8">What Data Will Be Deleted</h2>

                <p>Upon account deletion, the following data will be <strong>permanently removed</strong>:</p>
                <ul class="space-y-2">
                    <li>Your user profile (name, email, phone, photo)</li>
                    <li>Your login credentials</li>
                    <li>Your personal preferences and settings</li>
                    <li>Your Telegram connection data</li>
                </ul>

                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8">What Data May Be Retained</h2>

                <p>Some data may be retained for legitimate purposes:</p>
                <ul class="space-y-2">
                    <li><strong>Church records:</strong> Attendance records, event participation, and financial transactions linked to the church may be retained in anonymized form for the church's operational records.</li>
                    <li><strong>Audit logs:</strong> Records of actions performed within the platform may be retained for up to 90 days for security purposes.</li>
                </ul>

                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8">Timeline</h2>

                <p>Account deletion requests are processed within <strong>30 days</strong>. You will receive a confirmation email once your account has been deleted.</p>

                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8">Contact</h2>

                <p>If you have questions about data deletion, contact us at <a href="mailto:support@ministrify.app" class="text-blue-600 dark:text-blue-400">support@ministrify.app</a>.</p>
            </div>
        </div>
    </div>
</section>
@endsection
