@extends('layouts.app')

@section('title', 'Pengajuan Surat - SIWIL AI')
@section('page-title', 'Pengajuan Surat')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Daftar Pengajuan Surat</h1>
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Pengajuan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div class="data-card">
        @if($requests->count() > 0)
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nomor Surat</th>
                            <th>Nama Pemohon</th>
                            <th>Jenis Surat</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        @php
                            $finalLevel = $req->final_approval_level;
                            $nextStatus = $req->getNextActionStatusFor(Auth::user());
                            $canApprove = $req->canBeApprovedBy(Auth::user());
                            $nextLabel = $req->getNextActionLabelFor(Auth::user());
                            $docData = [
                                'id' => $req->id,
                                'request_number' => $req->request_number,
                                'type' => ucwords(str_replace('_', ' ', strtolower($req->type))),
                                'applicant' => $req->resident->full_name ?? 'Warga',
                                'nik' => $req->resident->nik ?? '-',
                                'area' => 'RT ' . $req->rt_number . ' / RW ' . $req->rw_number . ', Desa ' . $req->village_name,
                                'notes' => $req->notes ?? '-',
                                'attachment_url' => $req->attachment_path ? asset('storage/' . $req->attachment_path) : null,
                                'status' => $req->status,
                                'status_badge' => in_array($req->status, ['DISETUJUI', 'SELESAI']) ? 'badge-green' : ($req->status === 'DITOLAK' ? 'badge-red' : 'badge-yellow'),
                                'created_at' => $req->created_at->format('d M Y, H:i'),
                                'final_level' => $finalLevel,
                                'next_status' => $nextStatus,
                                'next_label' => $nextLabel,
                                'can_approve' => $canApprove,
                                'is_approved' => in_array($req->status, ['DISETUJUI', 'SELESAI']),
                                'print_url' => route('documents.print', $req->id),
                                'show_url' => route('documents.show', $req->id),
                                'update_url' => route('documents.update', $req->id),
                            ];
                        @endphp
                        <tr>
                            <td style="font-weight:600;">{{ $req->request_number }}</td>
                            <td>{{ $req->resident->full_name }}</td>
                            <td>{{ str_replace('_', ' ', $req->type) }}</td>
                            <td>
                                @if($req->status === 'SELESAI' || $req->status === 'DISETUJUI')
                                    <span class="badge badge-green">{{ $req->status }}</span>
                                @elseif($req->status === 'DITOLAK')
                                    <span class="badge badge-red">{{ $req->status }}</span>
                                @else
                                    <span class="badge badge-yellow">{{ $req->status }}</span>
                                @endif
                            </td>
                            <td>{{ $req->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display:inline-flex; gap:0.4rem; align-items:center;">
                                    <button type="button" class="btn-icon btn-icon-view" onclick='openDocModal(@json($docData))' title="Detail & Proses Surat" aria-label="Detail Surat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    @if(in_array($req->status, ['DISETUJUI', 'SELESAI']))
                                        <a href="{{ route('documents.print', $req->id) }}" target="_blank" class="btn-icon btn-icon-success" title="Cetak Surat" aria-label="Cetak Surat">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="6 9 6 2 18 2 18 9"/>
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                                <rect x="6" y="14" width="12" height="8"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem; border-top: 1px solid var(--border);">
                {{ $requests->links() }}
            </div>
        @else
            <div class="empty-state">
                Belum ada pengajuan surat.
            </div>
        @endif
    </div>

    {{-- Modal Popup Detail & Proses Pengajuan Surat --}}
    <div id="docModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 1rem; animation: fadeIn 0.2s ease;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: column;">
            {{-- Modal Header --}}
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-card); z-index: 2;">
                <div>
                    <h3 id="modalDocNumber" style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0;">Detail Surat</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;" id="modalDocType">Jenis Dokumen</div>
                </div>
                <button type="button" onclick="closeDocModal()" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;">&times;</button>
            </div>

            {{-- Modal Body --}}
            <div style="padding: 1.5rem;">
                {{-- Status Banner --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding: 0.75rem 1rem; border-radius: 10px; background: rgba(37, 99, 235, 0.05); border: 1px solid var(--border-glass);">
                    <div style="font-size: 0.85rem; color: var(--text-secondary);">
                        Status Saat Ini: <span id="modalDocStatusBadge" class="badge">PENDING</span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);" id="modalDocDate">-</div>
                </div>

                {{-- Alur Tingkat Verifikasi Surat --}}
                <div style="margin-bottom: 1.25rem; padding: 0.85rem 1rem; border-radius: 10px; background: var(--bg-body); border: 1px dashed var(--border-glass);">
                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                        Tingkat Kebutuhan Verifikasi
                    </div>
                    <div id="modalDocVerificationLevel" style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">
                        -
                    </div>
                </div>

                {{-- Info Pemohon Table --}}
                <table style="width: 100%; border: none; font-size: 0.9rem; margin-bottom: 1.25rem;">
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="width: 32%; color: var(--text-secondary); padding: 8px 0;">Pemohon</td>
                        <td style="font-weight: 600; padding: 8px 0;" id="modalDocApplicant">-</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 8px 0;">NIK</td>
                        <td style="font-weight: 600; padding: 8px 0; font-family: monospace;" id="modalDocNik">-</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 8px 0;">Wilayah</td>
                        <td style="font-weight: 600; padding: 8px 0;" id="modalDocArea">-</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 8px 0;">Keperluan</td>
                        <td style="padding: 8px 0;" id="modalDocNotes">-</td>
                    </tr>
                    <tr id="modalDocAttachmentRow" style="display: none;">
                        <td style="color: var(--text-secondary); padding: 8px 0;">Berkas Lampiran</td>
                        <td style="padding: 8px 0;">
                            <a id="modalDocAttachmentLink" href="#" target="_blank" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">Buka Berkas &rarr;</a>
                        </td>
                    </tr>
                </table>

                {{-- Action Box (Setujui / Tolak) --}}
                <div id="modalDocActionBox" style="padding-top: 1rem; border-top: 1px solid var(--border-glass);">
                    <div id="modalDocActionButtons" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                        {{-- Approve Form --}}
                        <form id="modalDocApproveForm" action="" method="POST" style="margin: 0;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" id="modalDocApproveStatus" value="">
                            <button type="button" onclick="confirmApproveDoc()" class="btn btn-primary" id="modalDocApproveBtn" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><polyline points="20 6 9 17 4 12"/></svg>
                                <span id="modalDocApproveBtnText">Setujui Surat</span>
                            </button>
                        </form>

                        {{-- Toggle Reject Button --}}
                        <button type="button" class="btn btn-danger" onclick="toggleDocRejectForm(true)">
                            Tolak Pengajuan
                        </button>

                        {{-- Print Button (if approved) --}}
                        <a id="modalDocPrintBtn" href="#" target="_blank" class="btn btn-success" style="display: none; align-items: center; gap: 0.5rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Cetak Surat Resmi
                        </a>
                    </div>

                    {{-- Reject Form (Inline) --}}
                    <form id="modalDocRejectForm" action="" method="POST" style="display: none; margin-top: 1rem; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; border-radius: 12px;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="DITOLAK">
                        <label class="form-label" style="color: #b91c1c; font-weight: 600; margin-bottom: 0.4rem; display: block;">
                            Alasan Penolakan (Wajib Diisi):
                        </label>
                        <textarea name="notes" class="form-textarea" required placeholder="Tuliskan alasan penolakan secara jelas agar diketahui pemohon..." style="width: 100%; min-height: 80px; font-size: 0.88rem;"></textarea>
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                            <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Tolak</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleDocRejectForm(false)">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-glass); background: var(--bg-body); display: flex; justify-content: space-between; align-items: center;">
                <a id="modalDocFullPageLink" href="#" style="font-size: 0.85rem; color: var(--accent-1); text-decoration: none; font-weight: 600;">
                    Buka Halaman Lengkap / Riwayat &rarr;
                </a>
                <button type="button" onclick="closeDocModal()" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    <script>
    let currentActiveDoc = null;

    function openDocModal(doc) {
        currentActiveDoc = doc;
        window.currentActiveDoc = doc;

        document.getElementById('modalDocNumber').innerText = doc.request_number;
        document.getElementById('modalDocType').innerText = doc.type;
        document.getElementById('modalDocApplicant').innerText = doc.applicant;
        document.getElementById('modalDocNik').innerText = doc.nik;
        document.getElementById('modalDocArea').innerText = doc.area;
        document.getElementById('modalDocNotes').innerText = doc.notes;
        document.getElementById('modalDocDate').innerText = doc.created_at;

        // Status Badge
        const badge = document.getElementById('modalDocStatusBadge');
        badge.innerText = doc.status.replace(/_/g, ' ');
        badge.className = 'badge ' + doc.status_badge;

        // Verification Level Description
        let levelDesc = '';
        if (doc.final_level === 'RT') {
            levelDesc = '✅ Verifikasi Cukup Sampai RT (Internal RT - Langsung Terbit setelah disetujui RT)';
        } else if (doc.final_level === 'RW') {
            levelDesc = '🔄 Alur Verifikasi: Warga ➔ RT ➔ Disetujui RW (Terbit setelah disetujui RW)';
        } else {
            levelDesc = '🏛️ Alur Verifikasi: Warga ➔ RT ➔ RW ➔ Kantor Desa (Terbit setelah disetujui Desa)';
        }
        document.getElementById('modalDocVerificationLevel').innerText = levelDesc;

        // Attachment
        const attachmentRow = document.getElementById('modalDocAttachmentRow');
        const attachmentLink = document.getElementById('modalDocAttachmentLink');
        if (doc.attachment_url) {
            attachmentRow.style.display = 'table-row';
            attachmentLink.href = doc.attachment_url;
        } else {
            attachmentRow.style.display = 'none';
        }

        // Full Page Link
        document.getElementById('modalDocFullPageLink').href = doc.show_url;

        // Action Buttons Setup
        const actionBox = document.getElementById('modalDocActionBox');
        const approveForm = document.getElementById('modalDocApproveForm');
        const rejectForm = document.getElementById('modalDocRejectForm');
        const approveBtn = document.getElementById('modalDocApproveBtn');
        const approveBtnText = document.getElementById('modalDocApproveBtnText');
        const approveStatus = document.getElementById('modalDocApproveStatus');
        const printBtn = document.getElementById('modalDocPrintBtn');

        toggleDocRejectForm(false);
        approveForm.action = doc.update_url;
        approveForm.setAttribute('action', doc.update_url);
        rejectForm.action = doc.update_url;
        rejectForm.setAttribute('action', doc.update_url);

        if (doc.can_approve) {
            actionBox.style.display = 'block';
            approveForm.style.display = 'inline-block';
            approveStatus.value = doc.next_status;
            approveStatus.setAttribute('value', doc.next_status);
            approveBtnText.innerText = doc.next_label;
            const rejectBtn = document.querySelector('#modalDocActionButtons .btn-danger');
            if (rejectBtn) rejectBtn.style.display = 'inline-block';
        } else {
            approveForm.style.display = 'none';
            const rejectBtn = document.querySelector('#modalDocActionButtons .btn-danger');
            if (rejectBtn) rejectBtn.style.display = 'none';
        }

        if (doc.is_approved) {
            printBtn.style.display = 'inline-flex';
            printBtn.href = doc.print_url;
            actionBox.style.display = 'block';
        } else {
            printBtn.style.display = 'none';
            if (!doc.can_approve) {
                actionBox.style.display = 'none';
            }
        }

        const modal = document.getElementById('docModalOverlay');
        modal.style.display = 'flex';
    }

    function closeDocModal() {
        document.getElementById('docModalOverlay').style.display = 'none';
    }

    function toggleDocRejectForm(show) {
        document.getElementById('modalDocRejectForm').style.display = show ? 'block' : 'none';
    }

    async function confirmApproveDoc(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        const form = document.getElementById('modalDocApproveForm');
        if (!form) return;

        // Pastikan action URL terpasang dengan benar
        let targetAction = form.getAttribute('action') || form.action;
        if ((!targetAction || targetAction === '' || targetAction === window.location.href) && currentActiveDoc) {
            targetAction = currentActiveDoc.update_url;
            form.action = targetAction;
            form.setAttribute('action', targetAction);
        }

        const statusInput = document.getElementById('modalDocApproveStatus');
        if (statusInput && (!statusInput.value || statusInput.value === '') && currentActiveDoc) {
            statusInput.value = currentActiveDoc.next_status;
            statusInput.setAttribute('value', currentActiveDoc.next_status);
        }

        const label = document.getElementById('modalDocApproveBtnText')?.innerText || 'Setujui Surat';
        
        let confirmed = true;
        if (typeof window.siwilConfirm === 'function') {
            confirmed = await window.siwilConfirm({
                title: label + '?',
                message: 'Apakah Anda yakin ingin memproses dan menyetujui pengajuan surat ini? Notifikasi status akan otomatis dikirimkan ke pemohon.',
                confirmText: 'Ya, ' + label,
                type: 'success'
            });
        }

        if (confirmed) {
            form.submit();
        }
    }

    document.getElementById('docModalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeDocModal();
    });
    </script>
@endsection
