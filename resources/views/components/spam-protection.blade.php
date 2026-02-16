@props(['action' => 'submit'])

{{-- Honeypot: hidden field that should remain empty --}}
<div style="display:none" aria-hidden="true">
    <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
</div>

{{-- Timestamp: filled by JS on page load --}}
<input type="hidden" name="_hp_started" x-init="$el.value = Math.floor(Date.now() / 1000)">

{{-- reCAPTCHA token: filled on form submit --}}
<input type="hidden" name="recaptcha_token" x-ref="recaptchaToken">
