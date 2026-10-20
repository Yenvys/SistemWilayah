<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengantar RT/RW - {{ $docRequest->request_number }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: "Times New Roman", Times, serif; color: #000; line-height: 1.6; font-size: 12pt; margin: 0; padding: 20px; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 20px; }
        .kop-surat h3 { margin: 2px 0; font-size: 13pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat h2 { margin: 2px 0; font-size: 15pt; text-transform: uppercase; font-weight: bold; }
        
        .title { text-align: center; margin-bottom: 25px; }
        .title h3 { text-decoration: underline; margin: 0; font-size: 14pt; text-transform: uppercase; }
        .title p { margin: 2px 0; font-size: 11pt; }

        .content { text-align: justify; margin-bottom: 20px; }
        .table-data { width: 90%; margin: 15px auto; border-collapse: collapse; }
        .table-data td { padding: 4px 8px; vertical-align: top; }
        
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 40px; text-align: center; page-break-inside: avoid; }
        .sig-box { display: flex; flex-direction: column; align-items: center; justify-content: space-between; min-height: 130px; font-size: 10.5pt; }
        .qr-stamp { display: flex; align-items: center; justify-content: center; gap: 10px; border: 1px dashed #475569; padding: 6px 12px; border-radius: 8px; background: #f8fafc; margin-top: 30px; max-width: 340px; margin-left: auto; margin-right: auto; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #e0f2fe; border: 1px solid #bae6fd; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <span style="color: #0369a1; font-family: sans-serif; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Surat Pengantar Resmi RT/RW (Siap Cetak / Download PDF)
        </span>
        <button onclick="window.print()" style="background: #0284c7; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; font-family: sans-serif; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak / Download PDF
        </button>
    </div>

    <div class="kop-surat">
        <h3>RUKUN TETANGGA {{ $docRequest->rt_number }} / RUKUN WARGA {{ $docRequest->rw_number }}</h3>
        <h2>DESA {{ strtoupper($docRequest->village_name) }} - KECAMATAN TERPADU</h2>
    </div>

    <div class="title">
        <h3>SURAT PENGANTAR / KETERANGAN</h3>
        <p>Nomor: {{ $docRequest->request_number }}</p>
    </div>

    <div class="content">
        <p>Pengurus RT {{ $docRequest->rt_number }} / RW {{ $docRequest->rw_number }} Desa {{ $docRequest->village_name }}, menerangkan bahwa:</p>
        
        <table class="table-data">
            <tr><td width="180">Nama Lengkap</td><td width="10">:</td><td><b>{{ $docRequest->resident->full_name }}</b></td></tr>
            <tr><td>NIK</td><td>:</td><td><b>{{ $docRequest->resident->nik }}</b></td></tr>
            <tr><td>Tempat/Tgl Lahir</td><td>:</td><td>{{ $docRequest->resident->birth_place ?? '-' }}, {{ $docRequest->resident->birth_date ? \Carbon\Carbon::parse($docRequest->resident->birth_date)->format('d F Y') : '-' }}</td></tr>
            <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $docRequest->resident->gender === 'LAKI_LAKI' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
            <tr><td>Pekerjaan</td><td>:</td><td>{{ $docRequest->resident->occupation ?? '-' }}</td></tr>
            <tr><td>Alamat Domisili</td><td>:</td><td>RT {{ $docRequest->rt_number }} / RW {{ $docRequest->rw_number }}, Desa {{ $docRequest->village_name }}</td></tr>
        </table>

        <p>Orang tersebut di atas adalah warga bertempat tinggal di wilayah RT {{ $docRequest->rt_number }} / RW {{ $docRequest->rw_number }} dan surat pengantar ini diberikan untuk keperluan: <b>{{ $docRequest->notes ?? 'Pengurusan Dokumen Resmi Kependudukan' }}</b>.</p>

        <p>Demikian Surat Pengantar ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature-grid">
        <div class="sig-box">
            <p>Pemohon Warga,</p>
            <br><br>
            <p><b>{{ $docRequest->resident->full_name }}</b></p>
        </div>

        <div class="sig-box">
            <p>Mengetahui,<br><b>Ketua RT {{ $docRequest->rt_number }}</b></p>
            <br><br>
            <p><b>( _________________ )</b></p>
        </div>

        <div class="sig-box">
            <p>Menyetujui,<br><b>Ketua RW {{ $docRequest->rw_number }}</b></p>
            <br><br>
            <p><b>( _________________ )</b></p>
        </div>
    </div>

    <div class="qr-stamp">
        <img src="{{ $docRequest->qr_code_url }}" alt="QR Code Verifikasi SIWIL" width="65" height="65">
        <div style="text-align: left; font-size: 8pt; font-family: sans-serif; color: #334155;">
            <strong style="color: #0f172a;">TERDOKUMENTASI & TERVERIFIKASI SIWIL AI</strong><br>
            No. Pengantar: {{ $docRequest->request_number }}<br>
            Status Approval: <span style="color: #059669; font-weight: bold;">{{ $docRequest->status }}</span><br>
            <i>Verifikasi keabsahan surat via QR Code.</i>
        </div>
    </div>
</body>
</html>
