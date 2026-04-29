@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Contacts landing')
@section('page-title', 'Messages de contact')
@section('page-description', 'Historique des messages envoyes depuis la page Contactez-nous.')

@section('header-badges')
    <span class="badge-soft">{{ $unreadCount }} non lu(s)</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="fw-bold">Demandes recues</div>
                <div class="small text-secondary">Les messages publics restent consultables ici par le SA.</div>
            </div>
            <a href="{{ route('super-admin.landing-page.edit') }}" class="btn btn-outline-dark btn-sm">Retour landing</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $submission->name }}</div>
                                <div class="small text-secondary">{{ $submission->email }}</div>
                                @if ($submission->phone)
                                    <div class="small text-secondary">{{ $submission->phone }}</div>
                                @endif
                            </td>
                            <td>{{ $submission->subject ?: 'Sans sujet' }}</td>
                            <td style="max-width:420px">
                                <div class="small text-secondary">{{ $submission->message }}</div>
                            </td>
                            <td class="small text-secondary">{{ $submission->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($submission->read_at)
                                    <span class="badge text-bg-light">Lu</span>
                                @else
                                    <span class="badge text-bg-warning">Nouveau</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @unless ($submission->read_at)
                                    <form method="POST" action="{{ route('super-admin.landing-page.contacts.read', $submission) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-dark">Marquer lu</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-5">Aucun message pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $submissions->links() }}
    </section>
@endsection
