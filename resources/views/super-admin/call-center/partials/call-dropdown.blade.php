@php
    $route = $route ?? '#';
    $buttonLabel = $buttonLabel ?? 'Appel';
    $placeholder = $placeholder ?? 'Résumé de l’appel, réponse obtenue, suite à donner...';
@endphp

<div class="dropdown">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        {{ $buttonLabel }}
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3 shadow border-0" style="min-width: 320px;">
        <form method="POST" action="{{ $route }}" class="d-grid gap-2" onclick="event.stopPropagation();">
            @csrf
            <div class="fw-semibold">Suivi call center</div>
            <div class="small text-secondary">Marquer l’usager comme appelé et conserver une note exploitable.</div>
            <textarea name="comment" class="form-control" rows="4" maxlength="1200" placeholder="{{ $placeholder }}" required></textarea>
            <button class="btn btn-dark btn-sm" type="submit">Enregistrer l’appel</button>
        </form>
    </div>
</div>
