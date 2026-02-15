<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\DonationCampaign;
use App\Models\Transaction;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Group;
use App\Models\GroupJoinRequest;
use App\Models\Ministry;
use App\Models\MinistryJoinRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    // Church main page
    public function church(string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $upcomingEvents = Event::where('church_id', $church->id)
            ->where('is_public', true)
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->take(6)
            ->get();

        $ministries = Ministry::where('church_id', $church->id)
            ->where('is_public', true)
            ->withCount('members')
            ->get();

        $groups = Group::where('church_id', $church->id)
            ->where('is_public', true)
            ->withCount('members')
            ->get();

        $campaigns = DonationCampaign::where('church_id', $church->id)
            ->where('is_active', true)
            ->get();

        // Website builder content
        $enabledSections = $church->enabled_sections;
        $staff = $church->public_staff;
        $sermons = $church->public_sermons;
        $galleries = $church->public_galleries;
        $faqs = $church->public_faqs;
        $testimonials = $church->public_testimonials;
        $blogPosts = $church->public_blog_posts;

        return view('public.church', compact(
            'church', 'upcomingEvents', 'ministries', 'groups', 'campaigns',
            'enabledSections', 'staff', 'sermons', 'galleries', 'faqs', 'testimonials', 'blogPosts'
        ));
    }

    // Events listing
    public function events(string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $events = Event::where('church_id', $church->id)
            ->where('is_public', true)
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->paginate(12);

        return view('public.events', compact('church', 'events'));
    }

    // Single event page
    public function event(string $slug, Event $event)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        if ($event->church_id !== $church->id || !$event->is_public) {
            abort(404);
        }

        $event->load('ministry');

        return view('public.event', compact('church', 'event'));
    }

    // Event registration
    public function registerForEvent(Request $request, string $slug, Event $event)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        if ($event->church_id !== $church->id || !$event->is_public || !$event->canAcceptRegistrations()) {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'guests' => 'nullable|integer|min:0|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['event_id'] = $event->id;
        $validated['church_id'] = $church->id;
        $validated['guests'] = $validated['guests'] ?? 0;

        // Check if already registered
        $existing = EventRegistration::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Ви вже зареєстровані на цю подію.');
        }

        // Check remaining spaces
        if ($event->registration_limit) {
            $totalGuests = 1 + $validated['guests'];
            if ($totalGuests > $event->remaining_spaces) {
                return back()->with('error', 'На жаль, недостатньо місць.');
            }
        }

        EventRegistration::create($validated);

        return back()->with('success', 'Дякуємо за реєстрацію! Ви отримаєте підтвердження на email.');
    }

    // Ministry page
    public function ministry(string $slug, string $ministrySlug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $ministry = Ministry::where('church_id', $church->id)
            ->where('slug', $ministrySlug)
            ->where('is_public', true)
            ->with(['leader', 'positions'])
            ->withCount('members')
            ->firstOrFail();

        $upcomingEvents = Event::where('ministry_id', $ministry->id)
            ->where('is_public', true)
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->take(3)
            ->get();

        return view('public.ministry', compact('church', 'ministry', 'upcomingEvents'));
    }

    // Ministry join request
    public function joinMinistry(Request $request, string $slug, string $ministrySlug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $ministry = Ministry::where('church_id', $church->id)
            ->where('slug', $ministrySlug)
            ->where('is_public', true)
            ->where('allow_registrations', true)
            ->firstOrFail();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'skills' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:500',
        ]);

        $validated['ministry_id'] = $ministry->id;

        MinistryJoinRequest::create($validated);

        return back()->with('success', 'Дякуємо за заявку! Ми зв\'яжемося з вами найближчим часом.');
    }

    // Group page
    public function group(string $slug, string $groupSlug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $group = Group::where('church_id', $church->id)
            ->where('slug', $groupSlug)
            ->where('is_public', true)
            ->with('leader')
            ->withCount('members')
            ->firstOrFail();

        return view('public.group', compact('church', 'group'));
    }

    // Group join request
    public function joinGroup(Request $request, string $slug, string $groupSlug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $group = Group::where('church_id', $church->id)
            ->where('slug', $groupSlug)
            ->where('is_public', true)
            ->where('allow_join_requests', true)
            ->firstOrFail();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:500',
        ]);

        $validated['group_id'] = $group->id;

        GroupJoinRequest::create($validated);

        return back()->with('success', 'Дякуємо за заявку! Ми зв\'яжемося з вами найближчим часом.');
    }

    // Donate page
    public function donate(string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $campaigns = DonationCampaign::where('church_id', $church->id)
            ->where('is_active', true)
            ->get();

        $ministries = Ministry::where('church_id', $church->id)
            ->where('is_public', true)
            ->get();

        $paymentService = new PaymentService($church);
        $paymentMethods = [
            'liqpay' => $paymentService->isLiqPayAvailable(),
            'monobank' => $paymentService->isMonobankAvailable(),
            'monobank_link' => $paymentService->getMonobankJarLink(),
        ];

        return view('public.donate', compact('church', 'campaigns', 'ministries', 'paymentMethods'));
    }

    // Process donation
    public function processDonation(Request $request, string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:100000',
            'donor_name' => 'nullable|string|max:255',
            'donor_email' => 'nullable|email|max:255',
            'donor_phone' => 'nullable|string|max:20',
            'campaign_id' => ['nullable', 'exists:donation_campaigns,id', function ($attr, $value, $fail) use ($church) {
                if ($value && \App\Models\DonationCampaign::where('id', $value)->where('church_id', $church->id)->doesntExist()) {
                    $fail('Обрана кампанія не належить цій церкві.');
                }
            }],
            'message' => 'nullable|string|max:500',
            'payment_method' => 'required|in:liqpay,monobank',
            'is_anonymous' => 'boolean',
        ]);

        $paymentService = new PaymentService($church);

        if ($validated['payment_method'] === 'liqpay') {
            if (!$paymentService->isLiqPayAvailable()) {
                return back()->with('error', 'LiqPay наразі недоступний.');
            }

            try {
                $paymentData = $paymentService->createLiqPayPayment($validated);

                // Return LiqPay form data for client-side redirect
                return view('public.liqpay-redirect', [
                    'church' => $church,
                    'data' => $paymentData['data'],
                    'signature' => $paymentData['signature'],
                ]);
            } catch (\Exception $e) {
                return back()->with('error', 'Помилка при створенні платежу: ' . $e->getMessage());
            }
        }

        if ($validated['payment_method'] === 'monobank') {
            if (!$paymentService->isMonobankAvailable()) {
                return back()->with('error', 'Monobank наразі недоступний.');
            }

            // Create pending donation record
            $donation = $paymentService->createMonobankPayment($validated);

            // Redirect to Monobank jar
            $jarLink = $paymentService->getMonobankJarLink();

            // Validate URL is from trusted Monobank domain
            $allowedDomains = ['send.monobank.ua', 'monobank.ua', 'mono.bank'];
            $parsedUrl = parse_url($jarLink);
            $host = $parsedUrl['host'] ?? '';

            if (!in_array($host, $allowedDomains)) {
                return back()->with('error', 'Невірне посилання Monobank.');
            }

            return redirect()->away($jarLink);
        }

        return back()->with('error', 'Невідомий метод оплати.');
    }

    // Donation success page
    public function donateSuccess(string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        return view('public.donate-success', compact('church'));
    }

    // LiqPay webhook callback
    public function liqpayCallback(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        if (!$data || !$signature) {
            return response()->json(['status' => 'error'], 400);
        }

        // Decode data to get church info
        $decodedData = json_decode(base64_decode($data), true);
        $orderId = $decodedData['order_id'] ?? null;

        if (!$orderId) {
            return response()->json(['status' => 'error'], 400);
        }

        // Find transaction and church
        $transaction = Transaction::where('order_id', $orderId)->first();
        if (!$transaction) {
            return response()->json(['status' => 'error'], 404);
        }

        $church = $transaction->church;
        $paymentService = new PaymentService($church);

        // Verify signature
        if (!$paymentService->verifyLiqPayCallback($data, $signature)) {
            return response()->json(['status' => 'error'], 403);
        }

        // Process callback
        $paymentService->processLiqPayCallback($decodedData);

        return response()->json(['status' => 'ok']);
    }

    // Contact page
    public function contact(string $slug)
    {
        $church = Church::where('slug', $slug)
            ->where('public_site_enabled', true)
            ->firstOrFail();

        return view('public.contact', compact('church'));
    }

}
