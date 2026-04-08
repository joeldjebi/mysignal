@php
    $builderId = $builderId ?? 'signal-field-builder';
    $fieldRows = collect($fields ?? [])->values();
@endphp

<div class="vstack gap-3" data-signal-field-builder="{{ $builderId }}">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <label class="form-label mb-1">Champs demandes a l usager <span class="text-secondary fw-normal">(facultatif)</span></label>
            <div class="small text-secondary">Vous pouvez laisser cette section vide si aucun champ complementaire n est necessaire pour ce signalement.</div>
        </div>
        <button type="button" class="btn btn-outline-dark btn-sm" data-add-signal-field>Ajouter un champ</button>
    </div>

    <div class="vstack gap-3" data-signal-field-list>
        @forelse ($fieldRows as $index => $field)
            <div class="border rounded-4 p-3 bg-light-subtle" data-signal-field-row>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Cle technique</label>
                        <input type="text" name="field_keys[]" value="{{ old('field_keys.'.$index, $field['key'] ?? '') }}" class="form-control" placeholder="photo_reference">
                        <div class="small text-secondary mt-1">Sans espace, par exemple `gps_location`.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Libelle visible</label>
                        <input type="text" name="field_labels[]" value="{{ old('field_labels.'.$index, $field['label'] ?? '') }}" class="form-control" placeholder="Reference photo">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type de champ</label>
                        <select name="field_types[]" class="form-select">
                            <option value="text" @selected(old('field_types.'.$index, $field['type'] ?? 'text') === 'text')>Texte court</option>
                            <option value="number" @selected(old('field_types.'.$index, $field['type'] ?? 'text') === 'number')>Nombre</option>
                            <option value="textarea" @selected(old('field_types.'.$index, $field['type'] ?? 'text') === 'textarea')>Texte long</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="field_required[]" value="{{ $index }}" @checked((bool) old('field_required.'.$index, $field['required'] ?? false)) data-field-required>
                            <label class="form-check-label">Oblig.</label>
                        </div>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-signal-field>Retirer</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="border rounded-4 p-3 bg-light-subtle" data-signal-field-row>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Cle technique</label>
                        <input type="text" name="field_keys[]" class="form-control" placeholder="photo_reference">
                        <div class="small text-secondary mt-1">Sans espace, par exemple `gps_location`.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Libelle visible</label>
                        <input type="text" name="field_labels[]" class="form-control" placeholder="Reference photo">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type de champ</label>
                        <select name="field_types[]" class="form-select">
                            <option value="text">Texte court</option>
                            <option value="number">Nombre</option>
                            <option value="textarea">Texte long</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="field_required[]" value="0" data-field-required>
                            <label class="form-check-label">Oblig.</label>
                        </div>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-signal-field>Retirer</button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@once
    <template id="signal-field-row-template">
        <div class="border rounded-4 p-3 bg-light-subtle" data-signal-field-row>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Cle technique</label>
                    <input type="text" name="field_keys[]" class="form-control" placeholder="photo_reference">
                    <div class="small text-secondary mt-1">Sans espace, par exemple `gps_location`.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Libelle visible</label>
                    <input type="text" name="field_labels[]" class="form-control" placeholder="Reference photo">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type de champ</label>
                    <select name="field_types[]" class="form-select">
                        <option value="text">Texte court</option>
                        <option value="number">Nombre</option>
                        <option value="textarea">Texte long</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="field_required[]" value="0" data-field-required>
                        <label class="form-check-label">Oblig.</label>
                    </div>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-signal-field>Retirer</button>
                </div>
            </div>
        </div>
    </template>
    <script>
        (() => {
            function syncBuilderIndexes(builder) {
                builder.querySelectorAll('[data-signal-field-row]').forEach((row, index) => {
                    const checkbox = row.querySelector('[data-field-required]');
                    if (checkbox) {
                        checkbox.value = String(index);
                    }
                });
            }

            document.querySelectorAll('[data-signal-field-builder]').forEach((builder) => {
                const list = builder.querySelector('[data-signal-field-list]');
                const addButton = builder.querySelector('[data-add-signal-field]');
                const template = document.getElementById('signal-field-row-template');

                syncBuilderIndexes(builder);

                addButton?.addEventListener('click', () => {
                    const fragment = template.content.cloneNode(true);
                    list.appendChild(fragment);
                    syncBuilderIndexes(builder);
                });

                builder.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('[data-remove-signal-field]');

                    if (!removeButton) {
                        return;
                    }

                    const rows = builder.querySelectorAll('[data-signal-field-row]');

                    if (rows.length === 1) {
                        rows[0].querySelectorAll('input[type="text"], textarea').forEach((input) => {
                            input.value = '';
                        });
                        rows[0].querySelectorAll('select').forEach((select) => {
                            select.selectedIndex = 0;
                        });
                        rows[0].querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                            checkbox.checked = false;
                        });
                        syncBuilderIndexes(builder);
                        return;
                    }

                    removeButton.closest('[data-signal-field-row]')?.remove();
                    syncBuilderIndexes(builder);
                });
            });
        })();
    </script>
@endonce
