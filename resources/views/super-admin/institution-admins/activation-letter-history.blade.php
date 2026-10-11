@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Historique des courriers')
@section('page-title', 'Historique des courriers')
@section('page-description', $institutionAdmin->organization?->name ?: 'Institution')

@section('header-badges')
    <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">Retour aux admins institutionnels</a>
    <a href="{{ route('super-admin.institution-admins.activation-letter.edit', $institutionAdmin) }}" class="btn btn-success">Préparer un courrier</a>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h5 class="mb-1">{{ $institutionAdmin->organization?->name ?: '-' }}</h5>
                <div class="text-secondary small">{{ $institutionAdmin->name }} · {{ $institutionAdmin->email }}</div>
            </div>
            <span class="badge-soft">{{ $letters->total() }} courrier{{ $letters->total() > 1 ? 's' : '' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Généré le</th>
                        <th>Par</th>
                        <th>Statut</th>
                        <th>Lot</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($letters as $letter)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $letter->letter_number ?: $letter->activation_code }}</div>
                                <div class="small text-secondary">{{ $letter->letter_subject }}</div>
                            </td>
                            <td>{{ $letter->generated_at?->format('d/m/Y à H:i') ?: $letter->created_at?->format('d/m/Y à H:i') }}</td>
                            <td>{{ $letter->creator?->name ?: 'Utilisateur supprimé' }}</td>
                            <td><span class="status-chip">{{ $letter->status === 'superseded' ? 'Remplacé' : $letter->status }}</span></td>
                            <td>{{ $letter->batch?->reference ?: 'Individuel' }}</td>
                            <td class="text-end"><a href="{{ route('super-admin.institution-admins.activation-letter.history.pdf', [$institutionAdmin, $letter]) }}" class="btn btn-sm btn-outline-dark">Télécharger le PDF</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun courrier généré pour cette institution.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">{{ $letters->links() }}</div>
    </section>
@endsection
