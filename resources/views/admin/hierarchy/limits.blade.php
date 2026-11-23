@extends('layouts.admin')

@section('title', 'Update Limit')

@php
    $limitTitle = match ($level) {
        'mini_master' => 'MINI MASTER UPDATE LIMIT',
        'master_master' => 'MASTER UPDATE LIMIT',
        'super_master' => 'SUPER MASTER UPDATE LIMIT',
        'agent_master' => 'AGENT UPDATE LIMIT',
        'client_master' => 'CLIENT UPDATE LIMIT',
        default => strtoupper($config['label']).' UPDATE LIMIT',
    };
@endphp

@section('content')
    <div class="classic-form-header">
        <span>{{ $limitTitle }}</span>
        <a class="classic-back-btn classic-back-btn--light" href="{{ route('admin.hierarchy.index', $level) }}">Back</a>
    </div>

    <div class="limit-panel">
        @foreach($users as $user)
            <form id="limit-form-{{ $user->id }}" method="POST" action="{{ route('admin.hierarchy.limits.update', $level) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
            </form>
        @endforeach

        <div class="table-wrap">
            <table class="data-table limit-table">
                <thead>
                    <tr>
                        <th>CODE <span class="th-search">⌕</span></th>
                        <th>NAME <span class="th-search">⌕</span></th>
                        <th>C.CHIP</th>
                        <th>ADD / MINUS LIMIT</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="mono">{{ $user->agent_code }}</td>
                            <td>{{ strtoupper($user->username) }}</td>
                            <td class="mono">{{ number_format($user->balance, 2) }}</td>
                            <td>
                                <input class="input limit-input" type="number" name="amount" min="0.01" step="0.01" placeholder="Enter amount" form="limit-form-{{ $user->id }}" required>
                            </td>
                            <td>
                                <div class="limit-actions">
                                    <button class="limit-add" type="submit" name="direction" value="add" form="limit-form-{{ $user->id }}">+ Add</button>
                                    <button class="limit-minus" type="submit" name="direction" value="minus" form="limit-form-{{ $user->id }}">− Minus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="no-data"><span>NO DATA</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links('partials.pagination') }}
    </div>
@endsection
