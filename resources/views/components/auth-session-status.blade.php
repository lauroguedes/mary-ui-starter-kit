@props(['status', 'type' => 'success'])

@if ($status)
    <x-mary-alert :title="$status" icon="c-check" class="alert-{{ $type }}" />
@endif
