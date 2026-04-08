@php
    $dialCodeOptions = $dialCodeOptions ?? [
        ['value' => '225', 'label' => '🇨🇮 +225 · CI'],
    ];
    $phoneFieldName = $name ?? 'phone';
    $phoneLabel = $label ?? 'Telephone';
    $phoneValue = (string) ($value ?? '');
    $selectedDialCode = (string) ($dialCodeOptions[0]['value'] ?? '225');
    $localPhoneValue = $phoneValue;

    foreach ($dialCodeOptions as $dialCodeOption) {
        $code = (string) $dialCodeOption['value'];

        if ($phoneValue !== '' && str_starts_with($phoneValue, $code)) {
            $selectedDialCode = $code;
            $localPhoneValue = substr($phoneValue, strlen($code));
            break;
        }
    }
@endphp
<div data-phone-field>
    <label class="form-label">{{ $phoneLabel }}</label>
    <div class="input-group">
        <select class="form-select flex-grow-0" name="{{ $phoneFieldName }}_dial_code" data-dial-code-select style="width: 132px; max-width: 132px; min-width: 132px;">
            @foreach ($dialCodeOptions as $dialCodeOption)
                <option value="{{ $dialCodeOption['value'] }}" @selected($selectedDialCode === (string) $dialCodeOption['value'])>{{ $dialCodeOption['label'] }}</option>
            @endforeach
        </select>
        <input type="text" class="form-control" name="{{ $phoneFieldName }}_local" value="{{ $localPhoneValue }}" @if (!empty($placeholder)) placeholder="{{ $placeholder }}" @endif>
    </div>
    <input type="hidden" name="{{ $phoneFieldName }}" value="{{ $phoneValue }}">
</div>
