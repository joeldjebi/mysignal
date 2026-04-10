<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('publicUserTypeSelect-{{ $mode }}');
        const businessFields = document.getElementById('publicUserBusinessFields-{{ $mode }}');
        const sectorFields = document.getElementById('publicUserSectorFields-{{ $mode }}');
        const summaryType = document.getElementById('publicUserSummaryType-{{ $mode }}');
        const summaryProfile = document.getElementById('publicUserSummaryProfile-{{ $mode }}');
        const summaryPricingLabel = document.getElementById('publicUserSummaryPricingLabel-{{ $mode }}');
        const summaryPricingAmount = document.getElementById('publicUserSummaryPricingAmount-{{ $mode }}');
        const summaryKind = document.getElementById('publicUserSummaryKind-{{ $mode }}');
        const hint = document.getElementById('publicUserTypeHint-{{ $mode }}');

        if (!select || !businessFields || !sectorFields) {
            return;
        }

        const businessInputs = businessFields.querySelectorAll('input, select, textarea');
        const sectorInputs = sectorFields.querySelectorAll('input, select, textarea');

        const syncBusinessFields = () => {
            const selectedOption = select.options[select.selectedIndex];
            const typeCode = String(selectedOption?.dataset.typeCode || '').toUpperCase();
            const showBusinessFields = typeCode === 'UPE';
            const showSectorFields = typeCode === 'UPE' || typeCode === 'UPTI';
            const typeName = selectedOption?.dataset.typeName || 'Type non selectionne';
            const pricingLabel = selectedOption?.dataset.pricingLabel || '-';
            const pricingAmount = selectedOption?.dataset.pricingAmount || '-';

            sectorFields.classList.toggle('business-fields-hidden', !showSectorFields);
            sectorInputs.forEach((input) => {
                input.disabled = !showSectorFields;
                input.required = showSectorFields;
            });

            businessFields.classList.toggle('business-fields-hidden', !showBusinessFields);
            businessInputs.forEach((input) => {
                input.disabled = !showBusinessFields;
                input.required = showBusinessFields;
            });

            if (summaryType) {
                summaryType.textContent = typeName;
            }

            if (summaryKind) {
                summaryKind.textContent = typeCode === 'UPE' ? 'Entreprise' : (typeCode === 'UPTI' ? 'Travailleur independant' : 'Particulier');
            }

            if (summaryProfile) {
                summaryProfile.textContent = typeCode === 'UPE'
                    ? 'Compte entreprise avec informations juridiques et administratives.'
                    : (typeCode === 'UPTI'
                        ? 'Compte professionnel simplifie pour travailleur independant.'
                        : 'Compte particulier avec informations personnelles simplifiees.');
            }

            if (summaryPricingLabel) {
                summaryPricingLabel.textContent = pricingLabel;
            }

            if (summaryPricingAmount) {
                summaryPricingAmount.textContent = pricingAmount;
            }

            if (hint) {
                hint.textContent = typeCode === 'UPE'
                    ? 'Le type selectionne attend des informations entreprise completes pour une gestion conforme du compte.'
                    : (typeCode === 'UPTI'
                        ? 'Le type selectionne attend un secteur d activite, sans les informations juridiques lourdes d une entreprise.'
                        : 'Le type selectionne attend uniquement les informations personnelles essentielles du particulier.');
            }
        };

        select.addEventListener('change', syncBusinessFields);
        syncBusinessFields();
    });
</script>
