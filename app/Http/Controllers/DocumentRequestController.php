<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentStatusRequest;
use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Services\DocumentRequestService;
use Illuminate\Support\Facades\Auth;

class DocumentRequestController extends Controller
{
    protected DocumentRequestService $documentRequestService;

    public function __construct(DocumentRequestService $documentRequestService)
    {
        $this->documentRequestService = $documentRequestService;
    }

    public function index()
    {
        $user = Auth::user();

        $requests = DocumentRequest::with(['resident.rt.rw.village', 'resident.family.rt.rw.village', 'verifiedBy'])
            ->accessibleBy($user)
            ->latest()
            ->paginate(10);

        return view('dashboard.documents.index', compact('requests'));
    }

    public function create()
    {
        $user = Auth::user();
        $resident = null;
        if ($user->role === 'WARGA') {
            $resident = Resident::where('nik', $user->username)->first();
        }

        return view('dashboard.documents.create', compact('resident'));
    }

    public function store(StoreDocumentRequest $request)
    {
        try {
            $this->documentRequestService->store(
                $request->validated(),
                Auth::user(),
                $request->file('attachment')
            );

            if ($request->header('referer') && str_contains($request->header('referer'), 'dashboard') && ! str_contains($request->header('referer'), 'documents/create')) {
                return redirect()->route('dashboard')->with('success', 'Pengajuan surat berhasil dibuat.');
            }

            return redirect()->route('documents.index')->with('success', 'Pengajuan surat berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $docRequest = DocumentRequest::accessibleBy($user)
            ->with([
                'resident.rt.rw.village',
                'resident.family.rt.rw.village',
                'histories.actor',
            ])->findOrFail($id);

        return view('dashboard.documents.show', compact('docRequest'));
    }

    public function updateStatus(UpdateDocumentStatusRequest $request, $id)
    {
        $user = Auth::user();
        $docRequest = DocumentRequest::accessibleBy($user)->findOrFail($id);

        $this->documentRequestService->updateStatus(
            $docRequest,
            $request->validated(),
            $user
        );

        return back()->with('success', 'Status pengajuan surat berhasil diperbarui.');
    }

    public function print($id)
    {
        $user = Auth::user();
        $docRequest = DocumentRequest::accessibleBy($user)
            ->with([
                'resident.rt.rw.village',
                'resident.family.rt.rw.village',
                'verifiedBy.resident',
                'rtApprovedBy',
                'rwApprovedBy',
                'desaApprovedBy',
            ])->findOrFail($id);

        if (! in_array($docRequest->status, ['DISETUJUI', 'SELESAI', 'DIAJUKAN_RW', 'DIAJUKAN_DESA'])) {
            return back()->withErrors('Surat belum disetujui, belum dapat dicetak.');
        }

        $level = $docRequest->final_approval_level;
        $viewName = match ($level) {
            'RT' => 'dashboard.documents.formats.rt_only',
            'RW' => 'dashboard.documents.formats.rw_only',
            'DESA' => 'dashboard.documents.formats.desa',
            default => 'dashboard.documents.formats.default',
        };

        if (! view()->exists($viewName)) {
            $viewName = 'dashboard.documents.formats.default';
        }

        return view($viewName, compact('docRequest'));
    }
}
