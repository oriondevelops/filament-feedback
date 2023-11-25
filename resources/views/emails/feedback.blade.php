<x-mail::message>
# New @if($feedback['isAnonymous']) Anonymous @endif Feedback Submitted

Here are the details:

**Feedback Type:** {{ $feedback['type'] }}

@if($feedback['type'] === 'other')
**Feedback Nature:**
{{ $feedback['feedbackNature'] ?? 'Not provided' }}
@endif

**Description:**
{{ $feedback['description'] }}

@if($feedback['type'] === 'feature')
**Reason for Feature:**
{{ $feedback['reason'] ?? 'Not provided' }}
@endif

@if($feedback['type'] === 'bug')
**Expected Behavior:**
{{ $feedback['expectedBehavior'] ?? 'Not provided' }}

**Steps to Reproduce:**
{{ $feedback['stepsToReproduce'] ?? 'Not provided' }}
@endif

@if(!empty($feedback['additionalNotes']))
**Additional Notes:**
{{ $feedback['additionalNotes'] }}
@endif

@if(! $feedback['isAnonymous'])
**Submitted by:** {{ $feedback['name'] }}

**Contact Email:** {{ $feedback['email'] }}
@endif

Regards,<br />
{{ config('app.name') }}
</x-mail::message>
