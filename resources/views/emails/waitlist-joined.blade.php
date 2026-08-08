<x-mail::message>
# New waitlist signup

Someone joined the Cutcost waitlist.

**Email:** {{ $signup->email }}  
@if ($signup->name)
**Name:** {{ $signup->name }}  
@endif
@if ($signup->shop_name)
**Shop:** {{ $signup->shop_name }}  
@endif
**Source:** {{ $signup->source ?: 'waitlist' }}  
**When:** {{ $signup->created_at?->timezone(config('app.timezone'))->format('D j M Y · g:i A') }}

Reply to this email to reach them directly.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
