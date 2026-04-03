@extends('layouts.app')
@php($title = 'Survey Templates')

@section('top_actions')
    <a class="btn" href="{{ route('organization.surveys.create') }}">+ New Template</a>
@endsection

@section('content')
    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Questions</th><th>Default</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @forelse($templates as $template)
                <tr>
                    <td>{{ $template->name }}</td>
                    <td>{{ $template->questions_count }}</td>
                    <td>{{ $template->is_default ? 'Yes' : 'No' }}</td>
                    <td>{{ $template->is_active ? 'Yes' : 'No' }}</td>
                    <td><a href="{{ route('organization.surveys.edit', $template) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No templates yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $templates->links() }}
@endsection
