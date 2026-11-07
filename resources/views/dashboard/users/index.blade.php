@extends('layouts.app')

@section('title', 'Manajemen User - SIWIL AI')
@section('page-title', 'Manajemen User')

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Manajemen User</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 5v14M5 12h14"/></svg>
            Tambah User
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- Filter & Search --}}
    <div class="data-card" style="margin-bottom: 1rem;">
        <div style="padding: 0.75rem 1.25rem;">
            <form method="GET" action="{{ route('users.index') }}" data-ajax-filter="true" data-ajax-target="#usersCardContainer" data-count-badge="#usersCountBadge" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <input type="text" name="search" class="form-input" placeholder="Cari username atau email..." value="{{ request('search') }}" style="max-width: 280px;">
                <select name="role" class="form-select" style="max-width: 180px;">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" style="max-width: 180px;">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request('search') || request('role') || request('status'))
                    <a href="{{ route('users.index') }}" class="btn btn-outline btn-sm reset-filter-btn">Reset</a>
                @endif
            </form>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom: 1rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="data-card" id="usersCardContainer">
        <div class="data-card-header">
            <span class="data-card-title">Daftar Pengguna</span>
            <span id="usersCountBadge" class="badge badge-blue">{{ $users->total() }} total</span>
        </div>
        @if($users->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama & NIK</th>
                        <th>Kontak</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Wilayah Tugas</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $i => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $i }}</td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-weight: 600; color: var(--text-primary); font-size: 0.95rem; line-height: 1.25;">
                                    {{ $u->resident->full_name ?? ($u->name ?? '-') }}
                                </span>
                                <span style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace; letter-spacing: 0.5px;">
                                    {{ $u->username }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem; font-family: monospace; letter-spacing: 0.3px;">
                                    {{ $u->phone_number ?? '-' }}
                                </span>
                                <span style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.25;">
                                    {{ $u->email ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleColors = [
                                    'SUPER_ADMIN' => 'badge-red',
                                    'ADMIN_DESA' => 'badge-blue',
                                    'RW' => 'badge-green',
                                    'RT' => 'badge-yellow',
                                    'WARGA' => 'badge-gray',
                                ];
                            @endphp
                            <span class="badge {{ $roleColors[$u->role] ?? 'badge-gray' }}">{{ $u->role }}</span>
                        </td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'badge-yellow',
                                    'active' => 'badge-green',
                                    'inactive' => 'badge-gray',
                                    'rejected' => 'badge-red',
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$u->status] ?? 'badge-gray' }}">{{ ucfirst($u->status) }}</span>
                        </td>
                        <td>
                            @if($u->role === 'RT' && $u->rt)
                                RT {{ $u->rt->number }} / RW {{ $u->rt->rw->number ?? '-' }} - {{ $u->rt->rw->village->name ?? '-' }}
                            @elseif($u->role === 'RW' && $u->rw)
                                RW {{ $u->rw->number }} - {{ $u->rw->village->name ?? '-' }}
                            @elseif($u->role === 'ADMIN_DESA' && $u->village)
                                Desa {{ $u->village->name }}
                            @else
                                {{ $u->village->name ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $u->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                @if($u->status === 'pending' && $u->id !== Auth::id())
                                    <form action="{{ route('users.verify', $u->id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="btn btn-primary btn-sm" style="background-color: var(--success); border-color: var(--success);">Terima</button>
                                    </form>
                                    <form action="{{ route('users.verify', $u->id) }}" method="POST" data-confirm="Tolak pendaftaran user ini?" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger);">Tolak</button>
                                    </form>
                                @endif
                                <button type="button" 
                                    class="btn-icon btn-icon-primary"
                                    data-id="{{ $u->id }}"
                                    data-username="{{ $u->username }}"
                                    data-fullname="{{ $u->resident->full_name ?? ($u->name ?? '') }}"
                                    data-phone="{{ $u->phone_number ?? '' }}"
                                    data-email="{{ $u->email ?? '' }}"
                                    data-address="{{ $u->address ?? ($u->resident->family->address ?? '') }}"
                                    data-role="{{ $u->role }}"
                                    data-village-id="{{ $u->village_id ?? '' }}"
                                    data-rw-id="{{ $u->rw_id ?? '' }}"
                                    data-rt-id="{{ $u->rt_id ?? '' }}"
                                    data-update-url="{{ route('users.update', $u) }}"
                                    onclick="openEditUserModal(this)"
                                    title="Edit Pengguna"
                                    aria-label="Edit Pengguna">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                
                                @if($u->id !== Auth::id())
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" data-confirm="Hapus user ini?" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon-danger" title="Hapus Pengguna" aria-label="Hapus Pengguna">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-glass);">
                {{ $users->withQueryString()->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">Belum ada data user.</div>
        @endif
    </div>

    {{-- Modal Pop-up Edit User --}}
    <div id="editUserModal" class="siwil-modal-overlay" style="display: none;">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Edit Data Pengguna</h3>
                    <p class="siwil-modal-subtitle">Perbarui informasi akun, data profil, dan penugasan wilayah.</p>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeEditUserModal()" title="Kembali / Tutup (Esc)" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:18px;height:18px;">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="editUserForm" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="siwil-modal-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="modal_username">NIK / Username <small style="color: var(--text-muted);">(Permanen)</small></label>
                            <input type="text" id="modal_username" class="form-input" readonly disabled style="background: var(--bg-body); opacity: 0.85; cursor: not-allowed; font-family: monospace; font-weight: 600;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_full_name">Nama Lengkap</label>
                            <input type="text" id="modal_full_name" name="full_name" class="form-input" placeholder="Nama Lengkap User">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_phone_number">No. HP / WhatsApp</label>
                            <input type="text" id="modal_phone_number" name="phone_number" class="form-input" placeholder="Contoh: 08123456789">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_email">Email</label>
                            <input type="email" id="modal_email" name="email" class="form-input" placeholder="user@email.com">
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="modal_address">Alamat</label>
                            <textarea id="modal_address" name="address" class="form-input" rows="2" placeholder="Alamat lengkap (nama jalan, no rumah, RT/RW)..." style="resize: vertical;"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_role">Role <span style="color: var(--danger);">*</span></label>
                            <select id="modal_role" name="role" class="form-select" required>
                                @foreach($allowedRoles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_password">Password Baru <small style="color: var(--text-muted);">(opsional)</small></label>
                            <input type="password" id="modal_password" name="password" class="form-input" placeholder="Kosongkan jika tidak diubah">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" id="modal_password_confirmation" name="password_confirmation" class="form-input" placeholder="Ulangi password baru">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_village_id">Wilayah Desa</label>
                            <select id="modal_village_id" name="village_id" class="form-select">
                                <option value="">-- Pilih Desa --</option>
                                @foreach($villages as $village)
                                    <option value="{{ $village->id }}">{{ $village->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="modal_rw_id">Wilayah RW</label>
                            <select id="modal_rw_id" name="rw_id" class="form-select">
                                <option value="">-- Pilih RW --</option>
                                @foreach($rws as $rw)
                                    <option value="{{ $rw->id }}">RW {{ $rw->number }} - {{ $rw->village->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label" for="modal_rt_id">Wilayah RT</label>
                            <select id="modal_rt_id" name="rt_id" class="form-select">
                                <option value="">-- Pilih RT --</option>
                                @foreach($rts as $rt)
                                    <option value="{{ $rt->id }}">RT {{ $rt->number }} - RW {{ $rt->rw->number ?? '' }} (Desa {{ $rt->rw->village->name ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditUserModal()">
                        Kembali
                    </button>
                    <button type="submit" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .siwil-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 10050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .siwil-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .siwil-modal-dialog {
            background: var(--bg-card, #1e293b);
            border: 1px solid var(--border-glass, rgba(255, 255, 255, 0.1));
            border-radius: 18px;
            width: 100%;
            max-width: 680px;
            max-height: min(92vh, 820px);
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.08);
            transform: scale(0.95) translateY(12px);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }

        .siwil-modal-overlay.active .siwil-modal-dialog {
            transform: scale(1) translateY(0);
        }

        #editUserForm {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
            margin: 0;
        }

        .siwil-modal-header {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-glass, rgba(255, 255, 255, 0.08));
            background: var(--bg-card);
        }

        .siwil-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.25rem 0;
        }

        .siwil-modal-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .siwil-modal-close {
            background: var(--bg-body);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .siwil-modal-close:hover {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.35);
            color: var(--danger, #ef4444);
            transform: scale(1.05);
        }

        .siwil-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        .siwil-modal-footer {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-glass, rgba(255, 255, 255, 0.08));
            background: var(--bg-card);
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        @media (max-width: 640px) {
            .siwil-modal-dialog {
                max-width: 100%;
                max-height: 96vh;
            }
            .siwil-modal-body div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }
            .siwil-modal-body div[style*="grid-column: span 2"] {
                grid-column: span 1 !important;
            }
        }
    </style>

    <script>
        function openEditUserModal(btn) {
            const id = btn.getAttribute('data-id');
            const username = btn.getAttribute('data-username');
            const fullName = btn.getAttribute('data-fullname');
            const phone = btn.getAttribute('data-phone');
            const email = btn.getAttribute('data-email');
            const address = btn.getAttribute('data-address');
            const role = btn.getAttribute('data-role');
            const villageId = btn.getAttribute('data-village-id');
            const rwId = btn.getAttribute('data-rw-id');
            const rtId = btn.getAttribute('data-rt-id');
            const updateUrl = btn.getAttribute('data-update-url');

            const form = document.getElementById('editUserForm');
            form.action = updateUrl;

            document.getElementById('modal_username').value = username || '-';
            document.getElementById('modal_full_name').value = fullName || '';
            document.getElementById('modal_phone_number').value = phone || '';
            document.getElementById('modal_email').value = email || '';
            document.getElementById('modal_address').value = address || '';
            document.getElementById('modal_password').value = '';
            document.getElementById('modal_password_confirmation').value = '';

            const roleSelect = document.getElementById('modal_role');
            if (roleSelect) {
                let roleExists = false;
                for (let i = 0; i < roleSelect.options.length; i++) {
                    if (roleSelect.options[i].value === role) {
                        roleSelect.selectedIndex = i;
                        roleExists = true;
                        break;
                    }
                }
                if (!roleExists && role) {
                    const opt = new Option(role, role);
                    roleSelect.add(opt);
                    roleSelect.value = role;
                }
            }

            document.getElementById('modal_village_id').value = villageId || '';
            document.getElementById('modal_rw_id').value = rwId || '';
            document.getElementById('modal_rt_id').value = rtId || '';

            const modal = document.getElementById('editUserModal');
            modal.style.display = 'flex';
            modal.offsetHeight; // trigger reflow for smooth transition
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeEditUserModal() {
            const modal = document.getElementById('editUserModal');
            if (!modal) return;
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 200);
        }

        // Close when clicking outside modal dialog (backdrop)
        document.getElementById('editUserModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditUserModal();
            }
        });

        // Close on Escape key press
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('editUserModal');
                if (modal && modal.classList.contains('active')) {
                    closeEditUserModal();
                }
            }
        });
    </script>
@endsection
