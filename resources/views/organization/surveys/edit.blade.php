@extends('layouts.app')
@php($title = 'Edit Survey Template')

@section('content')
    <div class="card">
        <h2>Edit Template</h2>
        <form method="POST" action="{{ route('organization.surveys.update', $survey) }}">
            @csrf
            @method('PUT')
            <div class="grid-2">
                <div>
                    <label>Name</label>
                    <input name="name" value="{{ old('name', $survey->name) }}" required>
                </div>
                <div>
                    <label>Description</label>
                    <input name="description" value="{{ old('description', $survey->description) }}">
                </div>
            </div>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="is_default" value="1" @checked(old('is_default', $survey->is_default))>
                Default template
            </label>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="is_active" value="1" @checked(old('is_active', $survey->is_active))>
                Active
            </label>
            <button class="btn" type="submit">Save Template</button>
        </form>
    </div>

    <div class="card">
        <h3>Add Question</h3>
        <form method="POST" action="{{ route('organization.surveys.questions.store', $survey) }}">
            @csrf
            <div class="grid-2">
                <div>
                    <label>Question label</label>
                    <input name="label" required>
                </div>
                <div>
                    <label>Type</label>
                    <select name="type" required>
                        @foreach(['short_text','long_text','single_choice','multi_choice','rating','nps','yes_no','phone','date'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Placeholder</label>
                    <input name="placeholder">
                </div>
                <div>
                    <label>Order</label>
                    <input type="number" min="0" name="order_index" value="{{ $survey->questions->count() + 1 }}">
                </div>
            </div>
            <label>Options (for single/multi choice, comma or newline separated)</label>
            <textarea name="options_text"></textarea>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" style="width:auto;" name="is_required" value="1">
                Required
            </label>
            <button class="btn" type="submit">Add Question</button>
        </form>
    </div>

    <div class="card">
        <h3>Questions</h3>
        @forelse($survey->questions as $question)
            <div style="border:1px solid #e5e7eb; border-radius:10px; padding:14px; margin-bottom:10px;">
                <form method="POST" action="{{ route('organization.surveys.questions.update', [$survey, $question]) }}">
                    @csrf
                    @method('PATCH')
                    <div class="grid-2">
                        <div>
                            <label>Label</label>
                            <input name="label" value="{{ $question->label }}" required>
                        </div>
                        <div>
                            <label>Type</label>
                            <select name="type" required>
                                @foreach(['short_text','long_text','single_choice','multi_choice','rating','nps','yes_no','phone','date'] as $type)
                                    <option value="{{ $type }}" @selected($question->type === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Placeholder</label>
                            <input name="placeholder" value="{{ $question->placeholder }}">
                        </div>
                        <div>
                            <label>Order</label>
                            <input type="number" min="0" name="order_index" value="{{ $question->order_index }}">
                        </div>
                    </div>
                    <label>Options</label>
                    <textarea name="options_text">{{ implode("\n", $question->options ?? []) }}</textarea>
                    <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" style="width:auto;" name="is_required" value="1" @checked($question->is_required)>
                        Required
                    </label>
                    <button class="btn secondary" type="submit">Update</button>
                </form>

                <form method="POST" action="{{ route('organization.surveys.questions.destroy', [$survey, $question]) }}" onsubmit="return confirm('Delete this question?');" style="margin-top:8px;">
                    @csrf
                    @method('DELETE')
                    <button class="btn light" type="submit">Delete question</button>
                </form>
            </div>
        @empty
            <p class="muted">No questions added yet.</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('organization.surveys.destroy', $survey) }}" onsubmit="return confirm('Delete this template?');">
        @csrf
        @method('DELETE')
        <button class="btn light" type="submit">Delete Template</button>
    </form>
@endsection
