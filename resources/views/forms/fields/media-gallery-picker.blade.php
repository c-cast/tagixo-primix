<tgx-media-picker-field
    id="{{ $id }}"
    v-model="{{ $statePath }}"
    @if($multiple) :multiple="true" @endif
    @if($maxFiles !== null) :max-files="{{ $maxFiles }}" @endif
    @if(!empty($acceptedTypes)) :accepted-types='@json($acceptedTypes)' @endif
    @if($disabled) disabled @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
></tgx-media-picker-field>
