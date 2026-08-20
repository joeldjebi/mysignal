@php
    $contact = $contact ?? null;
@endphp

@if ($contact)
    <span class="status-chip bg-success-subtle text-success border border-success-subtle">Appelé</span>
    <div class="small text-secondary mt-1">{{ $contact->called_at?->format('d/m/Y H:i') }}</div>
    <div class="small text-secondary">{{ $contact->calledBy?->name ?: 'Agent non renseigné' }}</div>
    @if (filled($contact->comment))
        <div class="small text-secondary text-truncate" style="max-width: 220px;" title="{{ $contact->comment }}">{{ $contact->comment }}</div>
    @endif
@else
    <span class="status-chip bg-warning-subtle text-warning border border-warning-subtle">Non appelé</span>
@endif
