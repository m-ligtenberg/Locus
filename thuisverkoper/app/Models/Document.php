<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'title',
        'type',
        'file_path',
        'is_signed',
        'signed_at',
        'signature_data',
        'status',
        'version',
        'original_document_id',
    ];

    protected $casts = [
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
        'signature_data' => 'array',
    ];

    const DOCUMENT_TYPES = [
        'koopovereenkomst' => 'Koopovereenkomst (Purchase Agreement)',
        'property_info' => 'Woninginformatie (Property Information)',
        'viewing_report' => 'Bezichtigingsrapport (Viewing Report)',
        'epc_certificate' => 'EPC Certificaat (Energy Certificate)',
        'service_contract' => 'Servicecontract (Service Contract)',
        'property_brochure' => 'Woningbrochure (Property Brochure)',
        'notary_deed' => 'Notariele Akte (Notarial Deed)',
        'mortgage_info' => 'Hypotheekinformatie (Mortgage Information)',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_SIGNATURE = 'pending_signature';
    const STATUS_SIGNED = 'signed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSigned($query)
    {
        return $query->where('is_signed', true);
    }

    public function scopeUnsigned($query)
    {
        return $query->where('is_signed', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getFileSizeAttribute()
    {
        if (file_exists(storage_path('app/public/' . $this->file_path))) {
            return filesize(storage_path('app/public/' . $this->file_path));
        }
        return 0;
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Relationship to original document (for versions)
     */
    public function originalDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'original_document_id');
    }

    /**
     * Relationship to document versions
     */
    public function versions()
    {
        return $this->hasMany(Document::class, 'original_document_id');
    }

    /**
     * Scope for documents by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('is_signed', false)
                    ->where('created_at', '<', now()->subDays(30));
    }

    /**
     * Scope for pending signature documents
     */
    public function scopePendingSignature($query)
    {
        return $query->where('is_signed', false)
                    ->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Get the document type label
     */
    public function getTypeLabel(): string
    {
        return self::DOCUMENT_TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get the current status of the document
     */
    public function getCurrentStatus(): string
    {
        if ($this->is_signed) {
            return self::STATUS_SIGNED;
        }

        if ($this->created_at->addDays(30)->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status ?? self::STATUS_DRAFT;
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return !$this->is_signed && $this->created_at->addDays(30)->isPast();
    }

    /**
     * Check if document can be signed
     */
    public function canBeeSigned(): bool
    {
        return !$this->is_signed && !$this->isExpired();
    }

    /**
     * Check if document can be edited
     */
    public function canBeEdited(): bool
    {
        return !$this->is_signed;
    }

    /**
     * Get signature verification data
     */
    public function getSignatureVerification(): array
    {
        if (!$this->is_signed || !isset($this->signature_data['signature_hash'])) {
            return ['verified' => false, 'message' => 'Document is niet ondertekend'];
        }

        $expectedHash = hash('sha256', $this->signature_data['signature_image'] ?? '');
        $actualHash = $this->signature_data['signature_hash'];
        $isValid = hash_equals($expectedHash, $actualHash);

        return [
            'verified' => $isValid,
            'signer_name' => $this->signature_data['signer_name'] ?? 'Onbekend',
            'signer_email' => $this->signature_data['signer_email'] ?? 'Onbekend',
            'signed_at' => $this->signed_at?->format('d-m-Y H:i:s'),
            'hash_match' => $isValid,
            'message' => $isValid ? 'Handtekening is geldig' : 'Handtekening is niet geldig'
        ];
    }

    /**
     * Generate document reference number
     */
    public function getReferenceNumber(): string
    {
        return sprintf(
            '%s-%06d-%s',
            strtoupper(substr($this->type, 0, 3)),
            $this->id,
            $this->created_at->format('Ymd')
        );
    }

    /**
     * Get download filename
     */
    public function getDownloadFilename(): string
    {
        $cleanTitle = \Illuminate\Support\Str::slug($this->title, '_');
        $suffix = $this->is_signed ? '_signed' : '';
        
        return "{$cleanTitle}{$suffix}.pdf";
    }

    /**
     * Check if user can access this document
     */
    public function canBeAccessedBy(\App\Models\User $user): bool
    {
        // Owner can always access
        if ($this->user_id === $user->id) {
            return true;
        }

        // Property owner can access property-related documents
        if ($this->property && $this->property->user_id === $user->id) {
            return true;
        }

        // Check if user is mentioned in signature data (for multi-party documents)
        if (isset($this->signature_data['authorized_users'])) {
            return in_array($user->email, $this->signature_data['authorized_users']);
        }

        return false;
    }

    /**
     * Get document metadata for API responses
     */
    public function getMetadata(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'status' => $this->getCurrentStatus(),
            'is_signed' => $this->is_signed,
            'is_expired' => $this->isExpired(),
            'can_be_signed' => $this->canBeeSigned(),
            'can_be_edited' => $this->canBeEdited(),
            'reference_number' => $this->getReferenceNumber(),
            'file_size' => $this->formatted_file_size,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'signed_at' => $this->signed_at?->format('d-m-Y H:i'),
            'property' => $this->property ? [
                'id' => $this->property->id,
                'title' => $this->property->title,
                'address' => $this->property->address,
            ] : null,
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Set default status when creating
        static::creating(function ($document) {
            if (!$document->status) {
                $document->status = self::STATUS_DRAFT;
            }
            if (!$document->version) {
                $document->version = 1;
            }
        });
    }
}