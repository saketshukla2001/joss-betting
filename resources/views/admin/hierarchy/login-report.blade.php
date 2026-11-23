@extends('layouts.admin')

@section('title', 'Login Report · '.$user->username)

@section('content')
    <div class="classic-form-header">
        <span>LOGIN REPORT · {{ strtoupper($user->username) }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.hierarchy.index', $level) }}">Back</a>
    </div>

    <div class="statement-panel">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Date</th><th>IP Address</th><th>User Agent</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="nowrap">{{ strtoupper($log->created_at->format('d-M-Y h:i A')) }}</td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                            <td>{{ $log->user_agent ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links('partials.pagination') }}
    </div>
@endsection
