@extends('layouts.app')

@section('title', 'Audit Logs - SIWIL AI')
@section('page-title', 'Audit Logs')

@section('styles')
<style>
    .log-action {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.2rem 0.6rem; border-radius: 6px;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
    }
    .log-action-create { background: #dcfce7; color: #166534; }
    .log-action-update { background: #dbeafe; color: #1d4ed8; }
    .log-action-delete { background: #fee2e2; color: #991b1b; }
    .log-action-default { background: #f1f5f9; color: #475569; }
    .log-details {
        font-size: 0.75rem; color: var(--text-muted);
        max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
</style>
@endsection

@section('content')
    <h1 class="page-title">Audit Logs</h1>

    {{-- Filter --}}
    <div class="data-card" style="margin-bottom: 1rem;">
        <div style="padding: 0.75rem 1.25rem;">
            <form method="GET" action="{{ route('audit-logs.index') }}" data-ajax-filter="true" data-ajax-target="#auditLogsCardContainer" data-count-badge="#auditLogsCountBadge" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" class="form-input" placeholder="Cari user atau entitas..." value="{{ request('search') }}" style="max-width: 240px;">
                <select name="action" class="form-select" style="max-width: 150px;">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
                <select name="entity" class="form-select" style="max-width: 150px;">
                    <option value="">Semua Entitas</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity }}" {{ request('entity') === $entity ? 'selected' : '' }}>{{ $entity }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request('search') || request('action') || request('entity'))
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-outline btn-sm reset-filter-btn">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="data-card" id="auditLogsCardContainer">
        <div class="data-card-header">
            <span class="data-card-title">Riwayat Aktivitas</span>
            <span id="auditLogsCountBadge" class="badge badge-blue">{{ $logs->total() }} total</span>
        </div>
        @if($logs->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Entitas</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td style="font-weight: 500; color: var(--text-primary);">{{ $log->user->username ?? 'System' }}</td>
                        <td>
                            @php
                                $actionClass = match($log->action) {
                                    'CREATE' => 'log-action-create',
                                    'UPDATE' => 'log-action-update',
                                    'DELETE' => 'log-action-delete',
                                    default => 'log-action-default',
                                };
                            @endphp
                            <span class="log-action {{ $actionClass }}">{{ $log->action }}</span>
                        </td>
                        <td><span class="badge badge-gray">{{ $log->entity_name }}</span></td>
                        <td>
                            <span class="log-details" title="{{ json_encode($log->details) }}">
                                @if($log->details)
                                    @foreach($log->details as $key => $value)
                                        {{ $key }}: {{ $value }}@if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    -
                                @endif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding: 1rem 1.25rem;">
                {{ $logs->withQueryString()->links() }}
            </div>
        @else
            <div class="empty-state">Belum ada audit log.</div>
        @endif
    </div>
@endsection
