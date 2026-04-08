<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('publicUserTypeSelect-{{ $mode }}');
        const businessFields = document.getElementById('publicUserBusinessFields-{{ $mode }}');
        const summaryType = document.getElementById('publicUserSummaryType-{{ $mode }}');
        const summaryProfile = document.getElementById('publicUserSummaryProfile-{{ $mode }}');
        const summaryPricingLabel = document.getElementById('publicUserSummaryPricingLabel-{{ $mode }}');
        const summaryPricingAmount = document.getElementById('publicUserSummaryPricingAmount-{{ $mode }}');
        const summaryKind = document.getElementById('publicUserSummaryKind-{{ $mode }}');
        const hint = document.getElementById('publicUserTypeHint-{{ $mode }}');

        if (!select || !businessFields) {
            return;
        }

        const businessInputs = businessFields.querySelectorAll('input, select, textarea');

        const syncBusinessFields = () => {
            const selectedOption = select.options[select.selectedIndex];
            const isBusiness = selectedOption?.dataset.profileKind === 'business';
            const typeName = selectedOption?.dataset.typeName || 'Type non selectionne';
            const pricingLabel = selectedOption?.dataset.pricingLabel || '-';
            const pricingAmount = selectedOption?.dataset.pricingAmount || '-';

            businessFields.classList.toggle('business-fields-hidden', !isBusiness);
            businessInputs.forEach((input) => {
                input.disabled = !isBusiness;
            });

            if (summaryType) {
                summaryType.textContent = typeName;
            }

            if (summaryKind) {
                summaryKind.textContent = isBusiness ? 'Entreprise' : 'Particulier';
            }

            if (summaryProfile) {
                summaryProfile.textContent = isBusiness
                    ? 'Compte entreprise avec informations juridiques et administratives.'
                    : 'Compte particulier avec informations personnelles simplifiees.';
            }

            if (summaryPricingLabel) {
                summaryPricingLabel.textContent = pricingLabel;
            }

            if (summaryPricingAmount) {
                summaryPricingAmount.textContent = pricingAmount;
            }

            if (hint) {
                hint.textContent = isBusiness
                    ? 'Le type selectionne attend des informations entreprise completes pour une gestion conforme du compte.'
                    : 'Le type selectionne attend uniquement les informations personnelles essentielles du particulier.';
            }
        };

        select.addEventListener('change', syncBusinessFields);
        syncBusinessFields();
    });
</script>
