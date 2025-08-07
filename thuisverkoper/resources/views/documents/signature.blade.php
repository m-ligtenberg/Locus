@extends('layouts.app')

@section('title', 'Document Ondertekenen - ' . $document->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Document Ondertekenen</h1>
                <p class="text-gray-600">{{ $document->title }}</p>
                <p class="text-sm text-gray-500 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $document->getTypeLabel() }}
                    </span>
                    <span class="ml-2">Referentie: {{ $document->getReferenceNumber() }}</span>
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Eigendom:</p>
                <p class="font-semibold text-gray-900">{{ $document->property->title }}</p>
                <p class="text-sm text-gray-500">{{ $document->property->address }}</p>
            </div>
        </div>
    </div>

    <!-- Document Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- PDF Preview -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Document Voorbeeld</h2>
                <p class="text-gray-600">Bekijk het document voordat u ondertekent</p>
            </div>
            <div class="p-6">
                <div class="bg-gray-100 rounded-lg p-4 h-96 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-600 mb-4">Bekijk het volledige document</p>
                        <a href="{{ route('documents.preview', $document) }}" 
                           target="_blank" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            PDF Bekijken
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Form -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Digitale Handtekening</h2>
                <p class="text-gray-600">Teken hieronder om het document te ondertekenen</p>
            </div>
            <div class="p-6">
                <form id="signatureForm" class="space-y-6">
                    @csrf
                    
                    <!-- Signer Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="signer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Volledige Naam *
                            </label>
                            <input type="text" 
                                   id="signer_name" 
                                   name="signer_name" 
                                   required
                                   value="{{ auth()->user()->name }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="signer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                E-mail Adres *
                            </label>
                            <input type="email" 
                                   id="signer_email" 
                                   name="signer_email" 
                                   required
                                   value="{{ auth()->user()->email }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Signature Pad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Handtekening *
                        </label>
                        <div class="border border-gray-300 rounded-md">
                            <canvas id="signaturePad" 
                                    width="500" 
                                    height="200" 
                                    class="w-full rounded-md cursor-crosshair"
                                    style="border: 1px solid #e5e7eb;"></canvas>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <button type="button" 
                                    id="clearSignature" 
                                    class="text-sm text-gray-600 hover:text-gray-800 underline">
                                Wissen
                            </button>
                            <p class="text-xs text-gray-500">Teken met uw muis of vinger in het veld hierboven</p>
                        </div>
                    </div>

                    <!-- Agreement Checkbox -->
                    <div class="flex items-start">
                        <input id="agreement" 
                               name="agreement" 
                               type="checkbox" 
                               required
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="agreement" class="ml-2 block text-sm text-gray-900">
                            Ik bevestig dat ik dit document heb gelezen en akkoord ga met alle voorwaarden. 
                            Door te ondertekenen geef ik mijn toestemming voor de digitale verwerking van dit document. *
                        </label>
                    </div>

                    <!-- Legal Notice -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Belangrijk:</h3>
                                <p class="mt-1 text-sm text-yellow-700">
                                    Uw digitale handtekening heeft dezelfde juridische waarde als een handgeschreven handtekening. 
                                    Deze handtekening wordt beveiligd opgeslagen en kan worden gebruikt als bewijs van uw akkoord.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between pt-6 border-t">
                        <a href="{{ route('documents.show', $document) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Terug
                        </a>
                        
                        <button type="submit" 
                                id="signButton"
                                disabled
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-300 disabled:cursor-not-allowed">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            Document Ondertekenen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <span class="ml-3 text-gray-700">Document wordt ondertekend...</span>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pad
    const canvas = document.getElementById('signaturePad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'white',
        penColor: 'black',
        minWidth: 1,
        maxWidth: 3
    });

    // Resize canvas
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }

    // Clear signature
    document.getElementById('clearSignature').addEventListener('click', function() {
        signaturePad.clear();
        validateForm();
    });

    // Form validation
    const form = document.getElementById('signatureForm');
    const signButton = document.getElementById('signButton');
    const nameInput = document.getElementById('signer_name');
    const emailInput = document.getElementById('signer_email');
    const agreementCheckbox = document.getElementById('agreement');

    function validateForm() {
        const isSignaturePadEmpty = signaturePad.isEmpty();
        const isNameFilled = nameInput.value.trim() !== '';
        const isEmailFilled = emailInput.value.trim() !== '';
        const isAgreementChecked = agreementCheckbox.checked;
        
        const isValid = !isSignaturePadEmpty && isNameFilled && isEmailFilled && isAgreementChecked;
        
        signButton.disabled = !isValid;
    }

    // Add event listeners
    signaturePad.addEventListener('endStroke', validateForm);
    nameInput.addEventListener('input', validateForm);
    emailInput.addEventListener('input', validateForm);
    agreementCheckbox.addEventListener('change', validateForm);

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (signaturePad.isEmpty()) {
            alert('Plaats eerst uw handtekening voordat u het document ondertekent.');
            return;
        }

        // Show loading modal
        document.getElementById('loadingModal').classList.remove('hidden');

        try {
            const signatureData = signaturePad.toDataURL('image/png');
            
            const response = await fetch('{{ route("documents.sign", $document) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    signature: signatureData,
                    signer_name: nameInput.value.trim(),
                    signer_email: emailInput.value.trim(),
                    signed_at: new Date().toISOString()
                })
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                const successModal = document.createElement('div');
                successModal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50';
                successModal.innerHTML = `
                    <div class="flex items-center justify-center min-h-screen">
                        <div class="bg-white rounded-lg p-8 shadow-xl max-w-md mx-4">
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Document Succesvol Ondertekend!</h3>
                                <p class="text-sm text-gray-500 mb-6">${result.message}</p>
                                <button onclick="window.location.href='${result.redirect_url}'" 
                                        class="w-full inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Naar Document
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(successModal);
                
                // Redirect after 3 seconds
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 3000);
                
            } else {
                throw new Error(result.message || 'Er is een fout opgetreden bij het ondertekenen.');
            }
        } catch (error) {
            console.error('Signature error:', error);
            alert('Er is een fout opgetreden bij het ondertekenen: ' + error.message);
        } finally {
            // Hide loading modal
            document.getElementById('loadingModal').classList.add('hidden');
        }
    });

    // Initial resize and validation
    resizeCanvas();
    validateForm();

    // Handle window resize
    window.addEventListener('resize', resizeCanvas);
});
</script>
@endpush