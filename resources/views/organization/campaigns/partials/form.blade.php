<div class="grid-2">
    <div>
        <label>Name</label>
        <input name="name" value="{{ old('name', $campaign?->name) }}" required>
    </div>
    <div>
        <label>Title</label>
        <input name="title" value="{{ old('title', $campaign?->title) }}" required>
    </div>
    <div>
        <label>CTA text</label>
        <input name="cta_text" value="{{ old('cta_text', $campaign?->cta_text) }}">
    </div>
    <div>
        <label>CTA URL</label>
        <input name="cta_url" value="{{ old('cta_url', $campaign?->cta_url) }}">
    </div>
    <div>
        <label>Image URL</label>
        <input name="image_url" value="{{ old('image_url', $campaign?->image_url) }}">
    </div>
    <div>
        <label>Display rule</label>
        <select name="display_rule">
            @foreach(['all' => 'All guests','new_guest' => 'New guests only','returning_guest' => 'Returning guests only'] as $key => $label)
                <option value="{{ $key }}" @selected(old('display_rule', $campaign?->display_rule ?? 'all') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Starts at</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($campaign?->starts_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div>
        <label>Ends at</label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($campaign?->ends_at)->format('Y-m-d\TH:i')) }}">
    </div>
</div>
<label>Body</label>
<textarea name="body">{{ old('body', $campaign?->body) }}</textarea>
<label style="display:flex;align-items:center;gap:8px;">
    <input type="checkbox" style="width:auto;" name="is_active" value="1" @checked(old('is_active', $campaign?->is_active ?? true))>
    Active campaign
</label>
