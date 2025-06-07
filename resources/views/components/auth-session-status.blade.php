@props(['status'])

@if ($status)
    <x-mary-alert :title="$status" icon="c-check" class="alert-success" />
@endif
