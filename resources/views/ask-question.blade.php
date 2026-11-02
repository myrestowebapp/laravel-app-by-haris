@extends('layouts.public')
@section('title', 'Ask an Islamic Question — Darul Ifta Kauzariyya')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    :root {
        --ask-primary: #052961;
        --ask-primary-dark: #031b40;
        --ask-gold: #c9a84c;
        --ask-gold-light: #fdfaf3;
        --ask-bg: #f8fafc;
        --ask-border: #e2e8f0;
    }

    .ask-page-canvas {
        background-color: var(--ask-bg);
        min-height: 85vh;
        padding-top: 1.5rem;
        padding-bottom: 5rem;
    }

    /* ── Hero Banner ── */
    .ask-hero-card {
        background: linear-gradient(135deg, #052961 0%, #0d3b82 60%, #1e4b9c 100%);
        border-radius: 22px;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        padding: 2rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 12px 36px rgba(5, 41, 97, 0.12);
    }

    @media (min-width: 768px) {
        .ask-hero-card {
            padding: 2.5rem 3rem;
            border-radius: 26px;
        }
    }

    .ask-hero-pattern {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(201, 168, 76, 0.15) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.8;
        pointer-events: none;
    }

    .ask-hero-inner {
        position: relative;
        z-index: 2;
    }

    .ask-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(201, 168, 76, 0.18);
        border: 1px solid rgba(201, 168, 76, 0.4);
        color: #fce7b2;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 9999px;
        margin-bottom: 0.75rem;
    }

    .ask-hero-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.6rem;
        line-height: 1.25;
        margin-bottom: 0.5rem;
    }

    @media (min-width: 768px) {
        .ask-hero-title {
            font-size: 2.1rem;
        }
    }

    .ask-hero-sub {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.92rem;
        max-width: 650px;
        margin-bottom: 0;
    }

    /* ── Main Form Card ── */
    .ask-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        padding: 1.5rem;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.03);
    }

    @media (min-width: 768px) {
        .ask-form-card {
            padding: 2.5rem;
            border-radius: 26px;
        }
    }

    .form-group-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.45rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-control-elevated {
        border-radius: 14px;
        border: 1px solid #cbd5e1;
        padding: 0.8rem 1.15rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #0f172a;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }

    .form-control-elevated:focus {
        border-color: #052961;
        box-shadow: 0 0 0 4px rgba(5, 41, 97, 0.08);
        background-color: #ffffff;
    }

    /* ── Quill Editor Modernization ── */
    #editor-container {
        border-radius: 0 0 14px 14px;
        border-color: #cbd5e1;
        font-family: 'Noto Serif Malayalam', 'Literata', 'Inter', serif;
        font-size: 1.08rem;
        min-height: 220px;
        background: #ffffff;
    }

    .ql-toolbar.ql-snow {
        border-radius: 14px 14px 0 0;
        border-color: #cbd5e1;
        background: #f8fafc;
        padding: 10px 14px;
    }

    .ql-container.ql-snow {
        border-color: #cbd5e1;
    }

    .ql-editor.ql-blank::before {
        font-family: 'Inter', sans-serif;
        font-style: normal;
        color: #94a3b8;
        font-size: 0.95rem;
    }

    /* ── File Upload Dropzone ── */
    .file-dropzone-box {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .file-dropzone-box:hover {
        border-color: #052961;
        background: #f1f5f9;
    }

    .file-upload-chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 1rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-top: 0.5rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    /* ── Submit CTA ── */
    .btn-submit-question {
        background: linear-gradient(135deg, #052961 0%, #0d3b82 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 1rem;
        padding: 0.85rem 2.25rem;
        border-radius: 9999px;
        border: none;
        box-shadow: 0 4px 18px rgba(5, 41, 97, 0.25);
        transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-submit-question:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(5, 41, 97, 0.35);
        color: #ffffff;
        background: linear-gradient(135deg, #031b40 0%, #052961 100%);
    }

    /* ── Guidelines Accordion Box ── */
    .guidelines-pill-box {
        background: #fdfaf3;
        border: 1px solid #f1ebd8;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
    }

    /* ── Hadiyya Card ── */
    .hadiyya-card {
        background: #ffffff;
        border: 1px solid rgba(201, 168, 76, 0.3);
        border-radius: 20px;
        padding: 1.25rem;
        box-shadow: 0 4px 18px rgba(201, 168, 76, 0.08);
        transition: all 0.25s ease;
        text-decoration: none;
        display: block;
    }

    .hadiyya-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(201, 168, 76, 0.18);
        border-color: #c9a84c;
    }
</style>
@endpush

@section('content')
<div class="ask-page-canvas">
    <div class="container" style="max-width: 960px;">

        {{-- 1. Hero Title Banner --}}
        <div class="ask-hero-card">
            <div class="ask-hero-pattern"></div>
            <div class="ask-hero-inner">
                <div class="ask-badge-pill">
                    <i class="bi bi-shield-check"></i>
                    <span>Official Scholarly Council Inquiry</span>
                </div>
                <h1 class="ask-hero-title">Ask an Islamic Question</h1>
                <p class="ask-hero-sub">
                    Submit your question on personal jurisprudence, worship, transactions, family, or faith matters to receive an authentic ruling verified by the qualified Muftis of Darul Ifta Kauzariyya.
                </p>
            </div>
        </div>

        {{-- 2. Flash Error Alerts --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm d-flex align-items-center gap-3" role="alert">
                <i class="bi bi-exclamation-octagon-fill text-danger fs-4"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1 fw-bold">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i> Please correct the issues below:
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- 3. Guidelines Box --}}
        <div class="guidelines-pill-box mb-4">
            <div class="row g-3 text-dark small">
                <div class="col-12 col-md-4 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span><strong>Authentic Fiqh:</strong> Based on Quran, Sunnah & Scholarly Consensus.</span>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-center gap-2">
                    <i class="bi bi-lock-fill text-primary fs-5"></i>
                    <span><strong>Confidential & Private:</strong> Personal identifiers remain protected.</span>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-center gap-2">
                    <i class="bi bi-award-fill text-warning fs-5"></i>
                    <span><strong>100% Free:</strong> No fees required for seeking guidance.</span>
                </div>
            </div>
        </div>

        {{-- Mandatory Mobile & Country Notice --}}
        @if(Auth::check() && (empty(Auth::user()->mobile) || empty(Auth::user()->country) || strtolower(Auth::user()->country) === 'not set'))
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3" style="background-color: #fff9ea; border: 1px solid #fae7ba !important;">
                <i class="bi bi-shield-exclamation text-warning fs-3"></i>
                <div>
                    <div class="fw-bold text-dark">Mobile Number & Country Are Mandatory</div>
                    <div class="text-muted small">To ensure fatwas are issued according to your geographical context and verified questioner identity, a valid mobile number and country are strictly required before submitting inquiries.</div>
                </div>
            </div>
        @endif

        {{-- 4. Main Form Card --}}
        <div class="ask-form-card mb-4">
            <form method="POST" action="{{ route('question.store') }}" enctype="multipart/form-data" id="askForm">
                @csrf

                {{-- Question Title / Summary --}}
                <div class="mb-4">
                    <label class="form-group-label">
                        <span>Question Subject / Summary</span>
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title" class="form-control form-control-elevated @error('title') is-invalid @enderror" 
                           value="{{ old('title') }}"
                           placeholder="e.g. Ruling on combining prayers during travel / Zakat on mutual funds..." 
                           maxlength="200" required>
                    <div class="form-text text-muted small mt-1">A concise, descriptive summary of your inquiry (max 200 characters).</div>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Question Full Body (Quill Rich Text Editor) --}}
                <div class="mb-4">
                    <label class="form-group-label">
                        <span>Full Question In Detail</span>
                        <span class="text-danger">*</span>
                    </label>
                    <div id="editor-container">{!! old('body') !!}</div>
                    <input type="hidden" name="body" id="body-input" value="{{ old('body') }}">
                    <div class="form-text text-muted small mt-1">Provide necessary background context clearly. You may type in Malayalam, English, Arabic, or Urdu.</div>
                    @error('body')<div class="text-danger small mt-1 fw-bold">{{ $message }}</div>@enderror
                </div>

                {{-- Madhab Selection --}}
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-group-label">
                            <span>Jurisprudence School (Madhab)</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="madhab_id" class="form-select form-control-elevated @error('madhab_id') is-invalid @enderror" required>
                            <option value="">Choose School of Thought...</option>
                            @foreach($madhabs as $m)
                                <option value="{{ $m->id }}" {{ old('madhab_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->name }} School
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small mt-1">Select the Fiqh school according to which you want the ruling.</div>
                        @error('madhab_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Supporting Files (Async Upload) --}}
                <div class="mb-4">
                    <label class="form-group-label">
                        <span>Supporting Documents / Images</span>
                        <span class="text-muted fw-normal small">(Optional)</span>
                    </label>
                    
                    <div class="file-dropzone-box" onclick="document.getElementById('file-uploader').click()">
                        <div class="mb-2 text-primary">
                            <i class="bi bi-cloud-arrow-up fs-2"></i>
                        </div>
                        <div class="fw-bold text-dark mb-1">Click to browse or drag & drop documents</div>
                        <div class="text-muted small">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB per file)</div>
                    </div>
                    
                    <input type="file" id="file-uploader" class="d-none" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <div id="upload-status-list" class="mt-2"></div>
                    <div id="hidden-uploads-container"></div>
                </div>

                {{-- Submission Action Row --}}
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 pt-3 border-top">
                    <button type="submit" id="submit-btn" class="btn-submit-question w-100 w-sm-auto">
                        <i class="bi bi-send-fill me-1"></i>
                        <span>Submit Question to Mufti Council</span>
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-light rounded-pill px-4 py-2.5 fw-semibold text-muted text-decoration-none w-100 w-sm-auto text-center">
                        Cancel & Return
                    </a>
                </div>
            </form>
        </div>
        
        {{-- 5. Hadiyya Contribution Card --}}
        <a href="https://pages.razorpay.com/daruliftakauzariyya" target="_blank" class="hadiyya-card text-center mb-4">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3">
                <img src="{{ asset('assets/logo/fecher.png') }}" alt="Darul Ifta Kauzariyya" class="img-fluid rounded-3" style="max-width: 140px; height: auto;">
                <div class="text-sm-start">
                    <h6 class="fw-800 text-dark mb-1 d-flex align-items-center justify-content-center justify-content-sm-start gap-1">
                        <span>🎁 Send Your Hadiyya to Darul Ifta Kauzariyya</span>
                    </h6>
                    <p class="text-muted small mb-0">Support authentic scholarly fatwa research and Islamic knowledge dissemination.</p>
                </div>
            </div>
        </a>

    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // --- Quill Editor Setup ---
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: { 
            toolbar: [
                ['bold', 'italic', 'underline'], 
                [{ 'list': 'ordered'}, { 'list': 'bullet' }], 
                [{ 'direction': 'rtl' }],
                ['clean']
            ] 
        },
        placeholder: 'Write your full question in detail here (Malayalam, English, Arabic or Urdu)...'
    });

    // --- Async File Upload Logic ---
    const fileInput = document.getElementById('file-uploader');
    const statusList = document.getElementById('upload-status-list');
    const hiddenContainer = document.getElementById('hidden-uploads-container');
    const submitBtn = document.getElementById('submit-btn');
    let activeUploads = 0;

    fileInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length === 0) return;

        Array.from(files).forEach(file => {
            uploadFile(file);
        });
        
        fileInput.value = '';
    });

    function uploadFile(file) {
        activeUploads++;
        updateSubmitButton();

        const fileId = 'file-' + Math.random().toString(36).substr(2, 9);
        const item = document.createElement('div');
        item.id = fileId;
        item.className = 'file-upload-chip';
        item.innerHTML = `
            <div class="d-flex align-items-center gap-2 overflow-hidden pe-2">
                <i class="bi bi-file-earmark-text-fill text-primary fs-5"></i>
                <span class="text-truncate fw-semibold small text-dark">${file.name}</span>
            </div>
            <span class="upload-status text-primary small d-flex align-items-center gap-1">
                <span class="spinner-border spinner-border-sm"></span>
                <span>Uploading...</span>
            </span>
        `;
        statusList.appendChild(item);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("question.files.upload") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            activeUploads--;
            if (data.success) {
                item.querySelector('.upload-status').innerHTML = `
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-bold me-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Ready
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1 remove-btn" data-path="${data.path}" title="Remove file">
                        <i class="bi bi-trash3"></i>
                    </button>
                `;
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'uploaded_files[]';
                hidden.value = JSON.stringify({
                    path: data.path,
                    name: data.original_name,
                    type: data.file_type,
                    size: data.file_size
                });
                item.setAttribute('data-hidden-id', fileId + '-hidden');
                hidden.id = fileId + '-hidden';
                hiddenContainer.appendChild(hidden);
            } else {
                item.querySelector('.upload-status').innerHTML = `<span class="text-danger fw-bold">Upload failed</span>`;
            }
            updateSubmitButton();
        })
        .catch(error => {
            activeUploads--;
            item.querySelector('.upload-status').innerHTML = `<span class="text-danger fw-bold">Network error</span>`;
            updateSubmitButton();
        });
    }

    function updateSubmitButton() {
        if (activeUploads > 0) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Uploading files...`;
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="bi bi-send-fill me-1"></i><span>Submit Question to Mufti Council</span>`;
        }
    }

    // Handle removal
    statusList.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-btn');
        if (btn) {
            const path = btn.getAttribute('data-path');
            const item = btn.closest('.file-upload-chip');
            const hiddenId = item.getAttribute('data-hidden-id');

            fetch('{{ route("question.files.delete") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ path: path })
            });

            item.remove();
            const hidden = document.getElementById(hiddenId);
            if (hidden) hidden.remove();
        }
    });

    let isSubmittingQuestion = false;
    document.getElementById('askForm').onsubmit = function(e) {
        if (isSubmittingQuestion) {
            if (e && e.preventDefault) e.preventDefault();
            return false;
        }

        var body = document.querySelector('input[name=body]');
        body.value = quill.root.innerHTML;
        if(quill.getText().trim().length === 0) {
            alert('Please enter your question.');
            return false;
        }
        
        isSubmittingQuestion = true;
        const btn = document.getElementById('submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Posting Question to Muftis...`;
        }
        
        return true;
    };
</script>
@endpush
@endsection
