<div class="plf-fields-multiple-outer">
    @if(!empty($values))
        @foreach($values as $value)
            <div {{ $attributes->merge(['class' => 'plf-field-outer']) }}>
                <button class="plf-field-delete" type="button">&#8722;</button>
                <button class="plf-field-remove" type="button">&#10006;</button>
                <div class="plf-field-body">
                    <img src="{{ $value['thumbnail'] }}" class="plf-field-img" data-placeholder="{{ $placeholder }}">
                    @if($value['needExtension'])
                        <span class="plf-field-body-extension">{{ $value['extension'] ?? '' }}</span>
                    @endif
                </div>
                <div class="plf-field-name">{{ $fileName ?? '' }}</div>
                <input type="hidden" name="{{ $name }}" value="{{ $value['value'] ?? '' }}">
            </div>
        @endforeach
    @else
        <div {{ $attributes->merge(['class' => 'plf-field-outer']) }}>
            <button class="plf-field-delete" type="button">&#8722;</button>
            <button class="plf-field-remove" type="button">&#10006;</button>
            <div class="plf-field-body">
                <img src="{{ $placeholder }}" class="plf-field-img" data-placeholder="{{ $placeholder }}">
            </div>
            <div class="plf-field-name">{{ $fileName ?? '' }}</div>
            <input type="hidden" name="{{ $name }}" value="">
        </div>
    @endif

    <div class="plf-fields-multiple-adding-outer">
        <button class="plf-fields-multiple-adding" type="button">+</button>
    </div>
    <div class="plf-fields-multiple-placeholder">
        <div {{ $attributes->merge(['class' => 'plf-field-outer']) }}>
            <button class="plf-field-delete" type="button">&#8722;</button>
            <button class="plf-field-remove" type="button">&#10006;</button>
            <div class="plf-field-body">
                <img src="{{ $placeholder }}" class="plf-field-img" data-placeholder="{{ $placeholder }}">
            </div>
            <div class="plf-field-name"></div>
            <input type="hidden" name="{{ $name }}" value="">
        </div>
    </div>
</div>
