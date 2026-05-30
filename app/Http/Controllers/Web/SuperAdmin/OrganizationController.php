<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Feature;
use App\Models\IncidentReport;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $query = Organization::query()->with(['application.features', 'organizationType', 'featureOverrides']);
        $features = Feature::query()->where('status', 'active')->orderBy('name')->get();

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('portal_key', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', request('application_id'));
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('organization_type_id'))) {
            $query->where('organization_type_id', request('organization_type_id'));
        }

        return view('super-admin.organizations.index', [
            'organizations' => $query->latest()->paginate(12)->withQueryString(),
            'applications' => Application::query()->with('features')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(),
            'features' => $features,
            'groupedFeatures' => $this->groupFeatures($features),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'application_id' => ['nullable', 'exists:applications,id'],
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'code' => ['required', 'string', 'max:60', 'unique:organizations,code'],
            'name' => ['required', 'string', 'max:180'],
            'portal_key' => ['nullable', 'string', 'max:60', 'unique:organizations,portal_key'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'commune' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $organization = Organization::query()->create([
            'application_id' => $attributes['application_id'] ?? null,
            'organization_type_id' => $attributes['organization_type_id'],
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'portal_key' => filled($attributes['portal_key'] ?? null) ? strtolower($attributes['portal_key']) : null,
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'commune' => $attributes['commune'] ?? null,
            'address' => $attributes['address'] ?? null,
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        $organization->loadMissing('application.features');
        $this->syncOrganizationFeatures($organization, $attributes['feature_ids'] ?? []);

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'L organisation a ete creee.');
    }

    public function import(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'organization_type_id' => ['nullable', 'exists:organization_types,id'],
            'organization_type_name' => ['nullable', 'string', 'max:180'],
            'csv_file' => ['required', 'file', 'max:5120'],
        ]);

        $rows = $this->readOrganizationImportRows($request->file('csv_file')->getRealPath());

        if ($rows === []) {
            throw ValidationException::withMessages([
                'csv_file' => ['Le fichier ne contient aucune ligne exploitable.'],
            ]);
        }

        $application = Application::query()->with('features')->findOrFail($attributes['application_id']);
        $hasOrganizationTypeInFile = collect($rows)->contains(fn (array $row): bool => filled($row['type_organisation'] ?? null));

        if (! $hasOrganizationTypeInFile && blank($attributes['organization_type_id'] ?? null) && blank($attributes['organization_type_name'] ?? null)) {
            throw ValidationException::withMessages([
                'organization_type_id' => ['Selectionnez un type d organisation, renseignez un nouveau type, ou utilisez une colonne Type_organisation dans le fichier.'],
            ]);
        }

        $globalOrganizationType = $hasOrganizationTypeInFile ? null : $this->resolveOrganizationType($attributes);
        $organizationTypeCache = [];
        $createdOrganizations = 0;
        $createdAdmins = 0;

        DB::transaction(function () use ($rows, $application, $globalOrganizationType, $request, &$createdOrganizations, &$createdAdmins, &$organizationTypeCache): void {
            foreach ($rows as $index => $row) {
                $name = trim((string) ($row['nom'] ?? ''));

                if ($name === '') {
                    throw ValidationException::withMessages([
                        'csv_file' => ['La ligne '.($index + 2).' ne contient pas de nom.'],
                    ]);
                }

                $organizationType = $globalOrganizationType ?? $this->resolveOrganizationTypeFromRow($row, $index, $organizationTypeCache);
                $phone = $this->normalizeImportedPhone($row['mobile'] ?? null, $index);
                $code = $this->uniqueOrganizationCode($name);
                $organization = Organization::query()->create([
                    'application_id' => $application->id,
                    'organization_type_id' => $organizationType->id,
                    'code' => $code,
                    'name' => $name,
                    'portal_key' => $this->uniquePortalKey($code),
                    'phone' => $phone,
                    'commune' => trim((string) ($row['commune'] ?? '')) ?: null,
                    'address' => trim((string) ($row['adresse'] ?? $row['region_district'] ?? '')) ?: null,
                    'description' => 'Import CSV SA',
                    'status' => 'active',
                ]);

                $organization->setRelation('application', $application);
                $this->syncOrganizationFeatures($organization, $application->features->pluck('id')->all());

                User::query()->create([
                    'organization_id' => $organization->id,
                    'name' => $name,
                    'email' => $this->uniqueAdminEmail($name),
                    'phone' => $phone,
                    'password' => Hash::make('12345678'),
                    'is_super_admin' => false,
                    'status' => 'active',
                    'created_by' => $request->user()?->id,
                ]);

                $createdOrganizations++;
                $createdAdmins++;
            }
        });

        return redirect()->route('super-admin.organizations.index')
            ->with('success', "{$createdOrganizations} institution(s) et {$createdAdmins} admin(s) institutionnel(s) ont ete crees.");
    }

    public function edit(Organization $organization): View
    {
        $features = Feature::query()->where('status', 'active')->orderBy('name')->get();

        return view('super-admin.organizations.edit', [
            'organization' => $organization->load(['application.features', 'organizationType', 'featureOverrides']),
            'applications' => Application::query()->with('features')->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'organizationTypes' => OrganizationType::query()->where('status', 'active')->orderBy('name')->get(),
            'features' => $features,
            'groupedFeatures' => $this->groupFeatures($features),
        ]);
    }

    public function show(Organization $organization): View
    {
        $organization->load([
            'application.features',
            'organizationType',
            'featureOverrides',
        ]);

        $organizationId = $organization->id;

        $stats = [
            'admins_count' => User::query()->where('organization_id', $organizationId)->count(),
            'meters_count' => \App\Models\Meter::query()->where('organization_id', $organizationId)->count(),
            'reports_count' => IncidentReport::query()->where('organization_id', $organizationId)->count(),
            'resolved_reports_count' => IncidentReport::query()->where('organization_id', $organizationId)->where('status', 'resolved')->count(),
            'open_reports_count' => IncidentReport::query()->where('organization_id', $organizationId)->whereIn('status', ['submitted', 'in_progress'])->count(),
            'damages_count' => IncidentReport::query()->where('organization_id', $organizationId)->whereNotNull('damage_declared_at')->count(),
            'payments_count' => Payment::query()->whereHas('incidentReport', fn ($query) => $query->where('organization_id', $organizationId))->count(),
            'payments_total' => (float) Payment::query()->whereHas('incidentReport', fn ($query) => $query->where('organization_id', $organizationId))->where('status', 'confirmed')->sum('amount'),
        ];

        $recentAdmins = User::query()
            ->where('organization_id', $organizationId)
            ->latest()
            ->take(6)
            ->get();

        $recentReports = IncidentReport::query()
            ->with(['publicUser', 'meter', 'commune'])
            ->where('organization_id', $organizationId)
            ->latest()
            ->take(6)
            ->get();

        $reportStatusBreakdown = IncidentReport::query()
            ->selectRaw('status, count(*) as aggregate')
            ->where('organization_id', $organizationId)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $damageStatusBreakdown = IncidentReport::query()
            ->selectRaw("coalesce(damage_resolution_status, 'non_soumis') as status, count(*) as aggregate")
            ->where('organization_id', $organizationId)
            ->groupByRaw("coalesce(damage_resolution_status, 'non_soumis')")
            ->pluck('aggregate', 'status');

        return view('super-admin.organizations.show', [
            'organization' => $organization,
            'stats' => $stats,
            'recentAdmins' => $recentAdmins,
            'recentReports' => $recentReports,
            'reportStatusBreakdown' => $reportStatusBreakdown,
            'damageStatusBreakdown' => $damageStatusBreakdown,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $attributes = $request->validate([
            'application_id' => ['nullable', 'exists:applications,id'],
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'code' => ['required', 'string', 'max:60', 'unique:organizations,code,'.$organization->id],
            'name' => ['required', 'string', 'max:180'],
            'portal_key' => ['nullable', 'string', 'max:60', 'unique:organizations,portal_key,'.$organization->id],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'commune' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:features,id'],
        ]);

        $organization->update([
            'application_id' => $attributes['application_id'] ?? null,
            'organization_type_id' => $attributes['organization_type_id'],
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'portal_key' => filled($attributes['portal_key'] ?? null) ? strtolower($attributes['portal_key']) : null,
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'commune' => $attributes['commune'] ?? null,
            'address' => $attributes['address'] ?? null,
            'description' => $attributes['description'] ?? null,
        ]);

        $organization->loadMissing('application.features');
        $this->syncOrganizationFeatures($organization, $attributes['feature_ids'] ?? []);

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'L organisation a ete mise a jour.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->delete();

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'L organisation a ete supprimee.');
    }

    public function toggleStatus(Organization $organization): RedirectResponse
    {
        $organization->update([
            'status' => $organization->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de l organisation a ete mis a jour.');
    }

    private function groupFeatures($features)
    {
        return $features
            ->groupBy(function (Feature $feature): string {
                return match (true) {
                    str_starts_with($feature->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard institutionnel',
                    str_starts_with($feature->code, 'INSTITUTION_') => 'Acces institutionnels',
                    str_starts_with($feature->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres fonctionnalites',
                };
            })
            ->sortKeys();
    }

    private function syncOrganizationFeatures(Organization $organization, array $selectedFeatureIds): void
    {
        $selectedIds = collect($selectedFeatureIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $applicationFeatureIds = collect($organization->application?->features?->pluck('id')->all() ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $enabledOverrideIds = $selectedIds
            ->diff($applicationFeatureIds)
            ->values();

        $disabledOverrideIds = $applicationFeatureIds
            ->diff($selectedIds)
            ->values();

        $payload = $enabledOverrideIds
            ->mapWithKeys(fn (int $featureId) => [$featureId => ['enabled' => true]])
            ->all();

        foreach ($disabledOverrideIds as $featureId) {
            $payload[(int) $featureId] = ['enabled' => false];
        }

        $organization->featureOverrides()->sync($payload);
    }

    private function resolveOrganizationType(array $attributes): OrganizationType
    {
        if (filled($attributes['organization_type_id'] ?? null)) {
            return OrganizationType::query()->findOrFail($attributes['organization_type_id']);
        }

        return $this->findOrCreateOrganizationType((string) $attributes['organization_type_name']);
    }

    private function resolveOrganizationTypeFromRow(array $row, int $rowIndex, array &$cache): OrganizationType
    {
        $name = trim((string) ($row['type_organisation'] ?? ''));

        if ($name === '') {
            throw ValidationException::withMessages([
                'csv_file' => ['La ligne '.($rowIndex + 2).' ne contient pas de Type_organisation.'],
            ]);
        }

        $cacheKey = Str::of($name)->ascii()->lower()->squish()->toString();

        if (! isset($cache[$cacheKey])) {
            $cache[$cacheKey] = $this->findOrCreateOrganizationType($name);
        }

        return $cache[$cacheKey];
    }

    private function findOrCreateOrganizationType(string $name): OrganizationType
    {
        $name = trim($name);
        $code = $this->uniqueOrganizationTypeCode($name);

        return OrganizationType::query()->firstOrCreate(
            ['name' => $name],
            [
                'code' => $code,
                'description' => 'Cree automatiquement pendant un import CSV.',
                'status' => 'active',
            ],
        );
    }

    private function readOrganizationImportRows(string $path): array
    {
        if ($this->isXlsxFile($path)) {
            return $this->readXlsxOrganizationImportRows($path);
        }

        return $this->readCsvOrganizationImportRows($path);
    }

    private function readCsvOrganizationImportRows(string $path): array
    {
        $delimiter = $this->detectCsvDelimiter($path);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_file' => ['Impossible de lire le fichier CSV.'],
            ]);
        }

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $data = array_map(fn ($value) => $this->normalizeCsvValue($value), $data);

            if ($data === [null] || collect($data)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($value) => $this->normalizeCsvHeader((string) $value), $data);
                $this->validateImportHeaders($headers);

                continue;
            }

            $row = [];

            foreach ($headers as $key => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = $data[$key] ?? null;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsxOrganizationImportRows(string $path): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                'csv_file' => ['Impossible de lire le fichier Excel.'],
            ]);
        }

        $sharedStrings = $this->readXlsxSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw ValidationException::withMessages([
                'csv_file' => ['La premiere feuille du fichier Excel est introuvable.'],
            ]);
        }

        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false) {
            throw ValidationException::withMessages([
                'csv_file' => ['La premiere feuille du fichier Excel est invalide.'],
            ]);
        }

        $headers = null;
        $rows = [];

        foreach ($sheet->sheetData->row as $sheetRow) {
            $values = [];

            foreach ($sheetRow->c as $cell) {
                $reference = (string) ($cell['r'] ?? '');
                $columnIndex = $this->xlsxColumnIndex($reference);

                if ($columnIndex === null) {
                    $columnIndex = count($values);
                }

                $values[$columnIndex] = $this->xlsxCellValue($cell, $sharedStrings);
            }

            if ($values === [] || collect($values)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }

            ksort($values);
            $values = array_values($values);

            if ($headers === null) {
                $headers = array_map(fn ($value) => $this->normalizeCsvHeader((string) $value), $values);
                $this->validateImportHeaders($headers);
                continue;
            }

            $row = [];

            foreach ($headers as $key => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = $values[$key] ?? null;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function isXlsxFile(string $path): bool
    {
        $zip = new \ZipArchive();
        $opened = $zip->open($path) === true;

        if (! $opened) {
            return false;
        }

        $isXlsx = $zip->locateName('xl/workbook.xml') !== false
            && $zip->locateName('xl/worksheets/sheet1.xml') !== false;
        $zip->close();

        return $isXlsx;
    }

    private function readXlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $sharedStrings = simplexml_load_string($xml);

        if ($sharedStrings === false) {
            return [];
        }

        $values = [];

        foreach ($sharedStrings->si as $item) {
            if (isset($item->t)) {
                $values[] = $this->normalizeCsvValue((string) $item->t) ?? '';
                continue;
            }

            $text = '';

            foreach ($item->r as $run) {
                $text .= (string) ($run->t ?? '');
            }

            $values[] = $this->normalizeCsvValue($text) ?? '';
        }

        return $values;
    }

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 's') {
            $index = (int) ($cell->v ?? -1);

            return $sharedStrings[$index] ?? null;
        }

        if ($type === 'inlineStr') {
            return $this->normalizeCsvValue((string) ($cell->is->t ?? ''));
        }

        return $this->normalizeCsvValue((string) ($cell->v ?? ''));
    }

    private function xlsxColumnIndex(string $reference): ?int
    {
        if (! preg_match('/^([A-Z]+)/i', $reference, $matches)) {
            return null;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function validateImportHeaders(array $headers): void
    {
        $hasClassicFormat = empty(array_diff(['nom', 'commune', 'adresse', 'mobile'], $headers));
        $hasTypedFormat = empty(array_diff(['type_organisation', 'nom', 'commune', 'region_district'], $headers));

        if (! $hasClassicFormat && ! $hasTypedFormat) {
            throw ValidationException::withMessages([
                'csv_file' => ['Colonnes attendues : Nom, Commune, Adresse, Mobile ou Type_organisation, Nom, Commune, Region_District.'],
            ]);
        }
    }

    private function detectCsvDelimiter(string $path): string
    {
        $line = '';
        $handle = fopen($path, 'rb');

        if ($handle !== false) {
            while (($currentLine = fgets($handle)) !== false) {
                if (trim($currentLine) !== '') {
                    $line = $currentLine;
                    break;
                }
            }

            fclose($handle);
        }

        return substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
    }

    private function normalizeCsvHeader(string $header): string
    {
        return Str::of($header)
            ->replace("\xEF\xBB\xBF", '')
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->replace('address', 'adresse')
            ->replace('type_organization', 'type_organisation')
            ->toString();
    }

    private function normalizeCsvValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = str_replace("\xEF\xBB\xBF", '', (string) $value);

        if (! mb_check_encoding($text, 'UTF-8')) {
            $converted = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
            $text = $converted !== false ? $converted : iconv('Windows-1252', 'UTF-8//IGNORE', $text);
        }

        return $text;
    }

    private function uniqueOrganizationCode(string $name): string
    {
        $base = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_')
            ->limit(50, '')
            ->toString() ?: 'ORGANISATION';

        return $this->uniqueValue($base, fn (string $candidate): bool => Organization::query()->where('code', $candidate)->exists(), 60);
    }

    private function uniqueOrganizationTypeCode(string $name): string
    {
        $base = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')
            ->trim('_')
            ->limit(50, '')
            ->toString() ?: 'TYPE_ORGANISATION';

        return $this->uniqueValue($base, fn (string $candidate): bool => OrganizationType::query()->where('code', $candidate)->exists(), 60);
    }

    private function uniquePortalKey(string $code): string
    {
        $base = Str::of($code)
            ->ascii()
            ->lower()
            ->replace('_', '-')
            ->replaceMatches('/[^a-z0-9-]+/', '-')
            ->trim('-')
            ->limit(50, '')
            ->toString() ?: 'institution';

        return $this->uniqueValue($base, fn (string $candidate): bool => Organization::query()->where('portal_key', $candidate)->exists(), 60);
    }

    private function uniqueAdminEmail(string $organizationName): string
    {
        $words = Str::of($organizationName)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->explode(' ')
            ->filter()
            ->values();
        $significantWords = $words
            ->reject(fn (string $word): bool => in_array($word, ['a', 'au', 'aux', 'd', 'de', 'des', 'du', 'et', 'l', 'la', 'le', 'les'], true))
            ->values();

        $base = $significantWords->count() > 1
            ? $significantWords->map(fn (string $word): string => substr($word, 0, 1))->implode('')
            : (string) ($significantWords->first() ?: $words->first() ?: 'admin');

        $base = Str::of($base)->replaceMatches('/[^a-z0-9]+/', '')->limit(40, '')->toString() ?: 'admin';
        $email = "{$base}@mysignal.pro";
        $sequence = 2;

        while (User::query()->where('email', $email)->exists()) {
            $email = "{$base}{$sequence}@mysignal.pro";
            $sequence++;
        }

        return $email;
    }

    private function normalizeImportedPhone(?string $phone, int $rowIndex): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if ($digits === '') {
            return '90'.str_pad((string) ($rowIndex + 1), 8, '0', STR_PAD_LEFT);
        }

        if (strlen($digits) < 10) {
            return str_pad($digits, 10, '0', STR_PAD_LEFT);
        }

        return substr($digits, -10);
    }

    private function uniqueValue(string $base, callable $exists, int $maxLength): string
    {
        $candidate = substr($base, 0, $maxLength);
        $sequence = 2;

        while ($exists($candidate)) {
            $suffix = '_'.$sequence;
            $candidate = substr($base, 0, $maxLength - strlen($suffix)).$suffix;
            $sequence++;
        }

        return $candidate;
    }
}
