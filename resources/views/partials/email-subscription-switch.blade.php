@once
    <link rel="stylesheet" href="{{ asset('css/email-subscription.css') }}">
@endonce
<label class="email-subscription" for="{{ $emailToggleId }}">
    <span>Email subscribe</span>
    <span class="email-subscription-control">
        <input type="checkbox" id="{{ $emailToggleId }}" name="subscribed" value="1"
               role="switch" @checked($emailSubscribed)
               @if(!empty($emailPreferenceUrl))
                   data-email-preference-url="{{ $emailPreferenceUrl }}"
                   data-csrf="{{ csrf_token() }}"
               @endif
               @if(!empty($emailHelpId)) aria-describedby="{{ $emailHelpId }}" @endif>
        <span class="email-subscription-track" aria-hidden="true"></span>
        <span class="email-subscription-status" aria-hidden="true"></span>
    </span>
</label>
