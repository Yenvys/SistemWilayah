@extends('layouts.app')

@section('title', 'Buat Musyawarah / Polling - SIWIL AI')
@section('page-title', 'Buat Polling Baru')

@section('content')
    <div class="data-card" style="max-width: 760px; margin: 0 auto;">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span class="data-card-title">Form Pembuatan Musyawarah / Polling</span>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                    Seluruh warga di wilayah yang dipilih akan otomatis menerima notifikasi untuk memberikan suara.
                </p>
            </div>
            <a href="{{ route('polls.index') }}" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('polls.store') }}" method="POST">
                @csrf

                {{-- Wilayah & Tingkat Musyawarah --}}
                @php $role = Auth::user()->role; @endphp
                <div style="background: rgba(99, 102, 241, 0.04); border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <label class="form-label" style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: var(--accent-1);"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                        Cakupan Wilayah Peserta Musyawarah
                    </label>

                    @if($role === 'RT')
                        <input type="hidden" name="level" value="RT">
                        <div style="font-size: 0.875rem; color: var(--text-primary);">
                            Tingkat: <span class="badge badge-yellow" style="font-weight: 600;">Rukun Tetangga (RT)</span>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.35rem;">
                                Polling ini khusus diikuti oleh warga di lingkungan RT Anda.
                            </div>
                        </div>
                    @elseif($role === 'RW')
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.8rem;">Tingkat Musyawarah</label>
                                <select name="level" id="poll_level" class="form-select" onchange="toggleScopeInputs()">
                                    <option value="RW">Seluruh Warga RW</option>
                                    <option value="RT">Khusus RT Tertentu</option>
                                </select>
                            </div>
                            <div class="form-group" id="rt_select_box" style="margin-bottom: 0; display: none;">
                                <label class="form-label" style="font-size: 0.8rem;">Pilih RT</label>
                                <select name="rt_id" class="form-select">
                                    <option value="">-- Pilih RT --</option>
                                    @foreach($rts as $rt)
                                        <option value="{{ $rt->id }}">RT {{ $rt->number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @elseif(in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA']))
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.8rem;">Tingkat Musyawarah</label>
                                <select name="level" id="poll_level" class="form-select" onchange="toggleScopeInputs()">
                                    <option value="DESA">Seluruh Warga Desa</option>
                                    <option value="RW">Tingkat RW</option>
                                    <option value="RT">Tingkat RT</option>
                                </select>
                            </div>
                            <div class="form-group" id="rw_select_box" style="margin-bottom: 0; display: none;">
                                <label class="form-label" style="font-size: 0.8rem;">Pilih RW</label>
                                <select name="rw_id" class="form-select">
                                    <option value="">-- Pilih RW --</option>
                                    @foreach($rws as $rw)
                                        <option value="{{ $rw->id }}">RW {{ $rw->number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" id="rt_select_box" style="margin-bottom: 0; display: none;">
                                <label class="form-label" style="font-size: 0.8rem;">Pilih RT</label>
                                <select name="rt_id" class="form-select">
                                    <option value="">-- Pilih RT --</option>
                                    @foreach($rts as $rt)
                                        <option value="{{ $rt->id }}">RT {{ $rt->number }} (RW {{ $rt->rw->number ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Judul Musyawarah / Polling <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="title" class="form-input" required placeholder="Contoh: Kesepakatan Jadwal Kerja Bakti Lingkungan" value="{{ old('title') }}">
                    @error('title') <small style="color: var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Penjelasan / Deskripsi Pokok Bahasan</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="Jelaskan latar belakang, rincian, atau konteks yang perlu dipahami warga sebelum memilih...">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Batas Waktu Pemilihan / Voting (Opsional)</label>
                    <input type="date" name="expires_at" class="form-input" min="{{ date('Y-m-d') }}" value="{{ old('expires_at') }}">
                    <small style="color: var(--text-muted); font-size: 0.78rem;">Kosongkan jika musyawarah berlangsung sampai ditutup manual oleh pengurus.</small>
                </div>

                <div class="form-group" id="options-container" style="margin-top: 1.5rem;">
                    <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Pilihan Opsi Jawaban (Minimal 2 Opsi) <span style="color: var(--danger);">*</span></span>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">Warga dapat memilih salah satu opsi ini</span>
                    </label>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;" id="options-list">
                        <div class="option-row" style="display: flex; gap: 0.5rem; align-items: center;">
                            <span class="option-number" style="font-weight: 700; color: var(--text-muted); width: 20px;">1.</span>
                            <input type="text" name="options[]" class="form-input" required placeholder="Opsi 1 (Contoh: Setuju / Opsi A)">
                        </div>
                        <div class="option-row" style="display: flex; gap: 0.5rem; align-items: center;">
                            <span class="option-number" style="font-weight: 700; color: var(--text-muted); width: 20px;">2.</span>
                            <input type="text" name="options[]" class="form-input" required placeholder="Opsi 2 (Contoh: Tidak Setuju / Opsi B)">
                        </div>
                    </div>
                    @error('options') <small style="color: var(--danger);">{{ $message }}</small> @enderror

                    <button type="button" id="add-option" class="btn btn-outline btn-sm" style="margin-top: 0.85rem; border-color: var(--accent-1); color: var(--accent-1);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Opsi Pilihan
                    </button>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 2rem; border-top: 1px solid var(--border-glass); padding-top: 1.25rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 600;">Terbitkan Polling & Kirim Notifikasi</button>
                    <a href="{{ route('polls.index') }}" class="btn btn-outline" style="padding: 0.65rem 1.25rem;">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function toggleScopeInputs() {
        const levelSelect = document.getElementById('poll_level');
        if (!levelSelect) return;
        const level = levelSelect.value;
        const rwBox = document.getElementById('rw_select_box');
        const rtBox = document.getElementById('rt_select_box');

        if (rwBox) {
            rwBox.style.display = (level === 'RW' || level === 'RT') ? 'block' : 'none';
        }
        if (rtBox) {
            rtBox.style.display = (level === 'RT') ? 'block' : 'none';
        }
    }

    document.getElementById('add-option').addEventListener('click', function() {
        const list = document.getElementById('options-list');
        const count = list.querySelectorAll('.option-row').length + 1;
        
        const row = document.createElement('div');
        row.className = 'option-row';
        row.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
        
        row.innerHTML = `
            <span class="option-number" style="font-weight: 700; color: var(--text-muted); width: 20px;">${count}.</span>
            <input type="text" name="options[]" class="form-input" required placeholder="Opsi ${count}">
            <button type="button" class="btn-icon btn-icon-delete" onclick="removeOptionRow(this)" title="Hapus Opsi" style="flex-shrink: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;
        list.appendChild(row);
        updateOptionNumbers();
    });

    function removeOptionRow(btn) {
        const list = document.getElementById('options-list');
        const rows = list.querySelectorAll('.option-row');
        if (rows.length > 2) {
            btn.closest('.option-row').remove();
            updateOptionNumbers();
        } else {
            alert('Minimal harus ada 2 opsi pilihan dalam musyawarah / polling.');
        }
    }

    function updateOptionNumbers() {
        const rows = document.querySelectorAll('#options-list .option-row');
        rows.forEach((row, index) => {
            const numSpan = row.querySelector('.option-number');
            if (numSpan) numSpan.textContent = (index + 1) + '.';
        });
    }
</script>
@endsection
