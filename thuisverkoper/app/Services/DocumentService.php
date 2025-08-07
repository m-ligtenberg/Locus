<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Property;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use ZipArchive;

class DocumentService
{
    /**
     * Generate a new document
     */
    public function generateDocument(array $data, User $user): Document
    {
        $property = Property::where('id', $data['property_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Generate PDF content based on document type
        $pdfContent = $this->generatePdfContent($data, $property, $user);
        
        // Generate unique filename
        $filename = $this->generateFilename($data['type'], $property->title, $user->id);
        
        // Store PDF file
        $filePath = "documents/{$user->id}/{$filename}";
        Storage::disk('public')->put($filePath, $pdfContent);

        // Create document record
        $document = Document::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'file_path' => $filePath,
            'is_signed' => false,
            'signature_data' => $this->prepareMetadata($data, $property, $user),
        ]);

        return $document;
    }

    /**
     * Update existing document
     */
    public function updateDocument(Document $document, array $data, User $user): Document
    {
        if ($document->is_signed) {
            throw new \Exception('Ondertekende documenten kunnen niet worden bewerkt.');
        }

        $property = Property::where('id', $data['property_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Generate new PDF content
        $pdfContent = $this->generatePdfContent($data, $property, $user);
        
        // Delete old file if exists
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Generate new filename
        $filename = $this->generateFilename($data['type'], $property->title, $user->id);
        $filePath = "documents/{$user->id}/{$filename}";
        
        // Store new PDF file
        Storage::disk('public')->put($filePath, $pdfContent);

        // Update document record
        $document->update([
            'property_id' => $property->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'file_path' => $filePath,
            'signature_data' => array_merge(
                $document->signature_data ?? [],
                $this->prepareMetadata($data, $property, $user),
                ['updated_at' => now()->toISOString()]
            ),
        ]);

        return $document->fresh();
    }

    /**
     * Sign a document
     */
    public function signDocument(Document $document, array $signatureData, User $user): Document
    {
        if ($document->is_signed) {
            throw new \Exception('Document is al ondertekend.');
        }

        // Generate signed PDF with embedded signature
        $signedPdfContent = $this->embedSignatureInPdf($document, $signatureData);
        
        // Create new filename for signed version
        $originalFilePath = $document->file_path;
        $signedFilePath = str_replace('.pdf', '_signed.pdf', $originalFilePath);
        
        // Store signed PDF
        Storage::disk('public')->put($signedFilePath, $signedPdfContent);

        // Update document record
        $document->update([
            'file_path' => $signedFilePath,
            'is_signed' => true,
            'signed_at' => Carbon::parse($signatureData['signed_at']),
            'signature_data' => array_merge(
                $document->signature_data ?? [],
                [
                    'signature_image' => $signatureData['signature'],
                    'signer_name' => $signatureData['signer_name'],
                    'signer_email' => $signatureData['signer_email'],
                    'signed_at' => $signatureData['signed_at'],
                    'signature_hash' => hash('sha256', $signatureData['signature']),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            ),
        ]);

        // Keep original unsigned version for reference
        $originalFileName = str_replace('.pdf', '_original.pdf', $originalFilePath);
        if (Storage::disk('public')->exists($originalFilePath)) {
            Storage::disk('public')->move($originalFilePath, $originalFileName);
        }

        return $document->fresh();
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Document $document): bool
    {
        if ($document->is_signed) {
            throw new \Exception('Ondertekende documenten kunnen niet worden verwijderd.');
        }

        // Delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete original file if exists
        $originalPath = str_replace('.pdf', '_original.pdf', $document->file_path);
        if (Storage::disk('public')->exists($originalPath)) {
            Storage::disk('public')->delete($originalPath);
        }

        return $document->delete();
    }

    /**
     * Regenerate document (create new version)
     */
    public function regenerateDocument(Document $originalDocument, User $user): Document
    {
        $property = $originalDocument->property;
        $originalData = $originalDocument->signature_data ?? [];

        // Extract original form data for regeneration
        $formData = [
            'property_id' => $property->id,
            'title' => $originalDocument->title . ' (Nieuwe versie)',
            'type' => $originalDocument->type,
        ];

        // Merge any preserved form data
        if (isset($originalData['form_data'])) {
            $formData = array_merge($formData, $originalData['form_data']);
        }

        return $this->generateDocument($formData, $user);
    }

    /**
     * Generate PDF content based on document type
     */
    protected function generatePdfContent(array $data, Property $property, User $user): string
    {
        $templateData = [
            'document' => $data,
            'property' => $property,
            'user' => $user,
            'generated_at' => now(),
        ];

        // Select template based on document type
        $template = $this->getTemplateForDocumentType($data['type']);
        
        // Configure PDF options
        $pdf = Pdf::loadView($template, $templateData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'enable_font_subsetting' => true,
                'debugCss' => false,
                'debugKeepTemp' => false,
                'debugPng' => false,
                'defaultPaperSize' => 'a4',
                'fontDir' => storage_path('fonts/'),
                'fontCache' => storage_path('fonts/'),
                'tempDir' => storage_path('tmp/'),
                'adminUsername' => 'admin',
                'adminPassword' => 'password',
            ]);

        return $pdf->output();
    }

    /**
     * Get template name for document type
     */
    protected function getTemplateForDocumentType(string $type): string
    {
        $templates = [
            'koopovereenkomst' => 'documents.templates.koopovereenkomst',
            'property_info' => 'documents.templates.property_info',
            'viewing_report' => 'documents.templates.viewing_report',
            'epc_certificate' => 'documents.templates.epc_certificate',
            'service_contract' => 'documents.templates.service_contract',
            'property_brochure' => 'documents.templates.property_brochure',
            'notary_deed' => 'documents.templates.notary_deed',
            'mortgage_info' => 'documents.templates.mortgage_info',
        ];

        return $templates[$type] ?? 'documents.templates.default';
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(string $type, string $propertyTitle, int $userId): string
    {
        $cleanTitle = Str::slug($propertyTitle, '_');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(6);
        
        return "{$type}_{$cleanTitle}_{$timestamp}_{$random}.pdf";
    }

    /**
     * Prepare metadata for document
     */
    protected function prepareMetadata(array $data, Property $property, User $user): array
    {
        return [
            'form_data' => $data,
            'property_data' => [
                'id' => $property->id,
                'title' => $property->title,
                'address' => $property->address,
                'postal_code' => $property->postal_code,
                'city' => $property->city,
            ],
            'user_data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'generation_info' => [
                'generated_at' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'version' => '1.0',
            ],
        ];
    }

    /**
     * Embed signature in PDF
     */
    protected function embedSignatureInPdf(Document $document, array $signatureData): string
    {
        // Load original PDF
        $originalPdfPath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($originalPdfPath)) {
            throw new \Exception('Origineel PDF bestand niet gevonden.');
        }

        // Get signature data and template data
        $property = $document->property;
        $user = $document->user;
        $originalData = $document->signature_data ?? [];

        $templateData = [
            'document' => $originalData['form_data'] ?? [],
            'property' => $property,
            'user' => $user,
            'signature' => $signatureData,
            'generated_at' => $document->created_at,
            'signed_at' => Carbon::parse($signatureData['signed_at']),
        ];

        // Generate signed version with signature overlay
        $template = $this->getTemplateForDocumentType($document->type);
        
        $pdf = Pdf::loadView($template, $templateData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'enable_font_subsetting' => true,
            ]);

        return $pdf->output();
    }

    /**
     * Verify document signature
     */
    public function verifyDocumentSignature(Document $document): array
    {
        if (!$document->is_signed) {
            return [
                'verified' => false,
                'message' => 'Document is niet ondertekend.'
            ];
        }

        $signatureData = $document->signature_data;
        
        if (!isset($signatureData['signature_hash'])) {
            return [
                'verified' => false,
                'message' => 'Geen geldige handtekening hash gevonden.'
            ];
        }

        // Verify signature hash
        $expectedHash = hash('sha256', $signatureData['signature_image'] ?? '');
        $actualHash = $signatureData['signature_hash'];
        
        $isValid = hash_equals($expectedHash, $actualHash);
        
        return [
            'verified' => $isValid,
            'signer_name' => $signatureData['signer_name'] ?? 'Onbekend',
            'signer_email' => $signatureData['signer_email'] ?? 'Onbekend',
            'signed_at' => $document->signed_at?->format('d-m-Y H:i:s'),
            'hash_match' => $isValid,
            'message' => $isValid ? 'Handtekening is geldig.' : 'Handtekening is niet geldig.'
        ];
    }

    /**
     * Get audit trail for document
     */
    public function getAuditTrail(Document $document): array
    {
        $trail = [];
        
        $signatureData = $document->signature_data ?? [];
        
        // Document creation
        $trail[] = [
            'action' => 'Aangemaakt',
            'timestamp' => $document->created_at,
            'user' => $document->user->name,
            'details' => 'Document gegenereerd: ' . $document->title
        ];

        // Updates
        if ($document->updated_at->gt($document->created_at)) {
            $trail[] = [
                'action' => 'Bijgewerkt',
                'timestamp' => $document->updated_at,
                'user' => $document->user->name,
                'details' => 'Document bijgewerkt'
            ];
        }

        // Signature
        if ($document->is_signed && $document->signed_at) {
            $trail[] = [
                'action' => 'Ondertekend',
                'timestamp' => $document->signed_at,
                'user' => $signatureData['signer_name'] ?? 'Onbekend',
                'details' => 'Document ondertekend door ' . ($signatureData['signer_name'] ?? 'onbekende persoon')
            ];
        }

        return collect($trail)->sortBy('timestamp')->values()->all();
    }

    /**
     * Get document versions
     */
    public function getDocumentVersions(Document $document): array
    {
        $versions = [];
        
        // Original version
        if ($document->file_path) {
            $originalPath = str_replace('.pdf', '_original.pdf', $document->file_path);
            if (Storage::disk('public')->exists($originalPath)) {
                $versions[] = [
                    'version' => 'Origineel',
                    'path' => $originalPath,
                    'created_at' => $document->created_at,
                    'is_signed' => false,
                ];
            }
        }

        // Current/signed version
        $versions[] = [
            'version' => $document->is_signed ? 'Ondertekend' : 'Huidig',
            'path' => $document->file_path,
            'created_at' => $document->is_signed ? $document->signed_at : $document->updated_at,
            'is_signed' => $document->is_signed,
        ];

        return $versions;
    }

    /**
     * Bulk delete documents
     */
    public function bulkDeleteDocuments($documents): int
    {
        $deleted = 0;
        
        foreach ($documents as $document) {
            try {
                if (!$document->is_signed) {
                    $this->deleteDocument($document);
                    $deleted++;
                }
            } catch (\Exception $e) {
                // Log error but continue with other documents
                \Log::error('Error deleting document ' . $document->id . ': ' . $e->getMessage());
            }
        }
        
        return $deleted;
    }

    /**
     * Bulk regenerate documents
     */
    public function bulkRegenerateDocuments($documents, User $user): int
    {
        $regenerated = 0;
        
        foreach ($documents as $document) {
            try {
                $this->regenerateDocument($document, $user);
                $regenerated++;
            } catch (\Exception $e) {
                // Log error but continue with other documents
                \Log::error('Error regenerating document ' . $document->id . ': ' . $e->getMessage());
            }
        }
        
        return $regenerated;
    }

    /**
     * Create ZIP archive of documents
     */
    public function createDocumentZip($documents, User $user): string
    {
        $zipFileName = 'documents_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($documents as $document) {
                $filePath = storage_path('app/public/' . $document->file_path);
                
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $document->title . '.pdf');
                }
            }
            
            $zip->close();
        } else {
            throw new \Exception('Kan ZIP bestand niet maken.');
        }
        
        return $zipPath;
    }

    /**
     * Process signature image for embedding
     */
    protected function processSignatureImage(string $signatureData): string
    {
        // Remove data URL prefix if present
        if (str_starts_with($signatureData, 'data:image')) {
            $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
        }

        // Decode base64 image
        $imageData = base64_decode($signatureData);
        
        if ($imageData === false) {
            throw new \Exception('Ongeldige handtekening afbeelding.');
        }

        // Process image with Intervention Image
        $image = Image::make($imageData);
        
        // Resize if too large
        if ($image->width() > 300 || $image->height() > 150) {
            $image->resize(300, 150, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Convert to PNG and return base64
        $image->encode('png');
        
        return 'data:image/png;base64,' . base64_encode($image->encoded);
    }

    /**
     * Get document statistics for user
     */
    public function getUserDocumentStats(User $user): array
    {
        $documents = Document::where('user_id', $user->id);
        
        return [
            'total' => $documents->count(),
            'signed' => $documents->signed()->count(),
            'unsigned' => $documents->unsigned()->count(),
            'expired' => $documents->unsigned()
                ->where('created_at', '<', now()->subDays(30))
                ->count(),
            'by_type' => $documents->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type'),
            'recent' => $documents->orderBy('created_at', 'desc')
                ->limit(5)
                ->with('property')
                ->get(),
        ];
    }
}