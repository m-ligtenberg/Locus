<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Property;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of documents
     */
    public function index(Request $request): View
    {
        $query = Document::with(['property', 'user'])
            ->where('user_id', Auth::id());

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by signature status
        if ($request->filled('signed')) {
            if ($request->signed === '1') {
                $query->signed();
            } else {
                $query->unsigned();
            }
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $properties = Property::where('user_id', Auth::id())->get();
        
        $documentTypes = Document::select('type')->distinct()->pluck('type');

        return view('documents.index', compact('documents', 'properties', 'documentTypes'));
    }

    /**
     * Show the form for creating a new document
     */
    public function create(Request $request): View
    {
        $properties = Property::where('user_id', Auth::id())->get();
        $selectedProperty = $request->property_id ? 
            Property::where('id', $request->property_id)->where('user_id', Auth::id())->first() : null;

        $documentTypes = [
            'koopovereenkomst' => 'Koopovereenkomst (Purchase Agreement)',
            'property_info' => 'Woninginformatie (Property Information)',
            'viewing_report' => 'Bezichtigingsrapport (Viewing Report)',
            'epc_certificate' => 'EPC Certificaat (Energy Certificate)',
            'service_contract' => 'Servicecontract (Service Contract)',
            'property_brochure' => 'Woningbrochure (Property Brochure)',
            'notary_deed' => 'Notariele Akte (Notarial Deed)',
            'mortgage_info' => 'Hypotheekinformatie (Mortgage Information)',
        ];

        return view('documents.create', compact('properties', 'selectedProperty', 'documentTypes'));
    }

    /**
     * Store a newly created document
     */
    public function store(DocumentRequest $request): RedirectResponse
    {
        try {
            $document = $this->documentService->generateDocument(
                $request->validated(),
                Auth::user()
            );

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document succesvol gegenereerd.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Fout bij het genereren van document: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified document
     */
    public function show(Document $document): View
    {
        $this->authorize('view', $document);
        
        $document->load(['property', 'user']);
        
        // Get document audit trail
        $auditTrail = $this->documentService->getAuditTrail($document);
        
        // Check if document has expired (for unsigned documents older than 30 days)
        $isExpired = !$document->is_signed && 
            $document->created_at->addDays(30)->isPast();

        return view('documents.show', compact('document', 'auditTrail', 'isExpired'));
    }

    /**
     * Show the form for editing the document
     */
    public function edit(Document $document): View
    {
        $this->authorize('update', $document);
        
        // Only allow editing unsigned documents
        if ($document->is_signed) {
            abort(403, 'Ondertekende documenten kunnen niet worden bewerkt.');
        }

        $properties = Property::where('user_id', Auth::id())->get();
        
        $documentTypes = [
            'koopovereenkomst' => 'Koopovereenkomst (Purchase Agreement)',
            'property_info' => 'Woninginformatie (Property Information)',
            'viewing_report' => 'Bezichtigingsrapport (Viewing Report)',
            'epc_certificate' => 'EPC Certificaat (Energy Certificate)',
            'service_contract' => 'Servicecontract (Service Contract)',
            'property_brochure' => 'Woningbrochure (Property Brochure)',
            'notary_deed' => 'Notariele Akte (Notarial Deed)',
            'mortgage_info' => 'Hypotheekinformatie (Mortgage Information)',
        ];

        return view('documents.edit', compact('document', 'properties', 'documentTypes'));
    }

    /**
     * Update the specified document
     */
    public function update(DocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);
        
        // Only allow updating unsigned documents
        if ($document->is_signed) {
            return back()->withErrors(['error' => 'Ondertekende documenten kunnen niet worden bewerkt.']);
        }

        try {
            $updatedDocument = $this->documentService->updateDocument(
                $document,
                $request->validated(),
                Auth::user()
            );

            return redirect()->route('documents.show', $updatedDocument)
                ->with('success', 'Document succesvol bijgewerkt.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Fout bij het bijwerken van document: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);
        
        // Don't allow deletion of signed documents
        if ($document->is_signed) {
            return back()->withErrors(['error' => 'Ondertekende documenten kunnen niet worden verwijderd.']);
        }

        try {
            $this->documentService->deleteDocument($document);
            
            return redirect()->route('documents.index')
                ->with('success', 'Document succesvol verwijderd.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Fout bij het verwijderen van document: ' . $e->getMessage()]);
        }
    }

    /**
     * Download the document PDF
     */
    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);
        
        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Documentbestand niet gevonden.');
        }

        return Response::download($filePath, $document->title . '.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $document->title . '.pdf"'
        ]);
    }

    /**
     * Preview the document in browser
     */
    public function preview(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);
        
        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Documentbestand niet gevonden.');
        }

        return Response::file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->title . '.pdf"'
        ]);
    }

    /**
     * Show signature page for document
     */
    public function signature(Document $document): View
    {
        $this->authorize('view', $document);
        
        // Only allow signing unsigned documents
        if ($document->is_signed) {
            return redirect()->route('documents.show', $document)
                ->with('info', 'Dit document is al ondertekend.');
        }

        // Check if document has expired
        if ($document->created_at->addDays(30)->isPast()) {
            return redirect()->route('documents.show', $document)
                ->withErrors(['error' => 'Dit document is verlopen en kan niet meer worden ondertekend.']);
        }

        return view('documents.signature', compact('document'));
    }

    /**
     * Process document signature
     */
    public function sign(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);
        
        $request->validate([
            'signature' => 'required|string',
            'signer_name' => 'required|string|max:255',
            'signer_email' => 'required|email',
            'signed_at' => 'required|date'
        ]);

        // Only allow signing unsigned documents
        if ($document->is_signed) {
            return response()->json([
                'success' => false,
                'message' => 'Dit document is al ondertekend.'
            ], 400);
        }

        // Check if document has expired
        if ($document->created_at->addDays(30)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Dit document is verlopen en kan niet meer worden ondertekend.'
            ], 400);
        }

        try {
            $signedDocument = $this->documentService->signDocument(
                $document,
                $request->only(['signature', 'signer_name', 'signer_email', 'signed_at']),
                Auth::user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Document succesvol ondertekend.',
                'document_id' => $signedDocument->id,
                'redirect_url' => route('documents.show', $signedDocument)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fout bij het ondertekenen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate document (create new version)
     */
    public function regenerate(Document $document): RedirectResponse
    {
        $this->authorize('update', $document);
        
        try {
            $newDocument = $this->documentService->regenerateDocument($document, Auth::user());
            
            return redirect()->route('documents.show', $newDocument)
                ->with('success', 'Document succesvol opnieuw gegenereerd.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Fout bij het regenereren van document: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk operations on documents
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,regenerate,download',
            'documents' => 'required|array',
            'documents.*' => 'exists:documents,id'
        ]);

        $documents = Document::whereIn('id', $request->documents)
            ->where('user_id', Auth::id())
            ->get();

        if ($documents->isEmpty()) {
            return back()->withErrors(['error' => 'Geen geldige documenten geselecteerd.']);
        }

        try {
            switch ($request->action) {
                case 'delete':
                    $deleted = $this->documentService->bulkDeleteDocuments($documents);
                    return back()->with('success', "{$deleted} documenten succesvol verwijderd.");
                
                case 'regenerate':
                    $regenerated = $this->documentService->bulkRegenerateDocuments($documents, Auth::user());
                    return back()->with('success', "{$regenerated} documenten succesvol opnieuw gegenereerd.");
                
                case 'download':
                    $zipPath = $this->documentService->createDocumentZip($documents, Auth::user());
                    return Response::download($zipPath)->deleteFileAfterSend();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Fout bij bulk actie: ' . $e->getMessage()]);
        }

        return back();
    }

    /**
     * Get document statistics (AJAX)
     */
    public function getStats(): JsonResponse
    {
        $userId = Auth::id();
        
        $stats = [
            'total' => Document::where('user_id', $userId)->count(),
            'signed' => Document::where('user_id', $userId)->signed()->count(),
            'unsigned' => Document::where('user_id', $userId)->unsigned()->count(),
            'expired' => Document::where('user_id', $userId)
                ->unsigned()
                ->where('created_at', '<', now()->subDays(30))
                ->count(),
            'by_type' => Document::where('user_id', $userId)
                ->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type'),
        ];

        return response()->json($stats);
    }

    /**
     * Search documents (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $query = Document::with(['property'])
            ->where('user_id', Auth::id());

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $documents = $query->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'type' => $document->type,
                    'property' => $document->property ? $document->property->title : null,
                    'is_signed' => $document->is_signed,
                    'created_at' => $document->created_at->format('d-m-Y H:i'),
                    'url' => route('documents.show', $document)
                ];
            });

        return response()->json($documents);
    }

    /**
     * Verify document signature
     */
    public function verifySignature(Document $document): JsonResponse
    {
        $this->authorize('view', $document);
        
        if (!$document->is_signed) {
            return response()->json([
                'verified' => false,
                'message' => 'Document is niet ondertekend.'
            ]);
        }

        $verification = $this->documentService->verifyDocumentSignature($document);
        
        return response()->json($verification);
    }

    /**
     * Get document versions
     */
    public function versions(Document $document): JsonResponse
    {
        $this->authorize('view', $document);
        
        $versions = $this->documentService->getDocumentVersions($document);
        
        return response()->json($versions);
    }
}