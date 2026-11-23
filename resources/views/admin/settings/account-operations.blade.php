@extends('layouts.admin')

@section('title', 'Account Operation')

@section('content')
    <div class="classic-form-header">
        <span>ACCOUNT OPERATION</span>
        <a class="classic-back-btn" href="{{ route('admin.settings.index') }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="table-wrap">
            <table class="data-table operation-table">
                <thead>
                    <tr><th>Date</th><th>Operation</th><th>Description</th></tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="nowrap">{{ strtoupper($log->created_at->format('d-M-Y h:i A')) }}</td>
                            <td><span class="operation-badge">{{ $log->operation }}</span></td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty"><strong>No operations recorded</strong></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links('partials.pagination') }}
    </div>
@endsection
