@extends('layouts.gestionnaire')

@section('title', 'تعديل الاستلام - ' . $reception->matricule_reception)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.receptions.index') }}">إدارة الاستلام</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.receptions.show', $reception->id_reception) }}">{{ $reception->matricule_reception }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Reception Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-edit fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $reception->matricule_reception }}</h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-user me-1"></i>
                                {{ $reception->membre->nom_complet }}
                                <span class="badge bg-warning ms-2">قابل للتعديل</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        تعديل بيانات الاستلام
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('gestionnaire.receptions.update', $reception->id_reception) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Membre Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="id_membre" class="form-label">
                                    المربي <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('id_membre') is-invalid @enderror" 
                                        id="id_membre" 
                                        name="id_membre" 
                                        required>
                                    <option value="">-- اختر المربي --</option>
                                    @foreach($membres as $membre)
                                        <option value="{{ $membre->id_membre }}" 
                                                {{ old('id_membre', $reception->id_membre) == $membre->id_membre ? 'selected' : '' }}
                                                data-carte="{{ $membre->numero_carte_nationale }}"
                                                data-phone="{{ $membre->telephone }}">
                                            {{ $membre->nom_complet }} - {{ $membre->numero_carte_nationale }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @error('id_membre')
                                        {{ $message }}
                                    @else
                                        يرجى اختيار المربي
                                    @enderror
                                </div>
                                <div id="membreInfo" class="mt-2" style="display: none;">
                                    <small class="text-muted">
                                        <i class="fas fa-id-card me-1"></i>
                                        <span id="membreCarte"></span>
                                        <i class="fas fa-phone ms-3 me-1"></i>
                                        <span id="membrePhone"></span>
                                    </small>
                                </div>
                            </div>

                            <!-- Date Reception -->
                            <div class="col-md-6 mb-3">
                                <label for="date_reception" class="form-label">
                                    تاريخ الاستلام <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('date_reception') is-invalid @enderror" 
                                       id="date_reception" 
                                       name="date_reception" 
                                       value="{{ old('date_reception', $reception->date_reception->format('Y-m-d')) }}" 
                                       max="{{ today()->format('Y-m-d') }}"
                                       required>
                                <div class="invalid-feedback">
                                    @error('date_reception')
                                        {{ $message }}
                                    @else
                                        يرجى اختيار تاريخ الاستلام
                                    @enderror
                                </div>
                            </div>

                            <!-- Quantite -->
                            <div class="col-md-12 mb-3">
                                <label for="quantite_litres" class="form-label">
                                    كمية اللبن (بالليتر) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('quantite_litres') is-invalid @enderror" 
                                           id="quantite_litres" 
                                           name="quantite_litres" 
                                           value="{{ old('quantite_litres', $reception->quantite_litres) }}" 
                                           min="0.01" 
                                           max="9999.99" 
                                           step="0.1" 
                                           placeholder="0.0"
                                           required>
                                    <span class="input-group-text">لتر</span>
                                    <div class="invalid-feedback">
                                        @error('quantite_litres')
                                            {{ $message }}
                                        @else
                                            يرجى إدخال كمية صحيحة
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    الحد الأدنى: 0.1 لتر | الحد الأقصى: 9999.99 لتر
                                </div>
                            </div>
                        </div>

                        <!-- Original vs New Comparison -->
                        <div class="alert alert-info" id="comparisonBox">
                            <h6><i class="fas fa-exchange-alt me-2"></i>مقارنة التغييرات:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">البيانات الأصلية:</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>المربي:</strong> {{ $reception->membre->nom_complet }}</li>
                                        <li><strong>التاريخ:</strong> {{ $reception->date_reception->format('d/m/Y') }}</li>
                                        <li><strong>الكمية:</strong> {{ number_format($reception->quantite_litres, 2) }} لتر</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">البيانات الجديدة:</h6>
                                    <ul class="list-unstyled small">
                                        <li><strong>المربي:</strong> <span id="newMembre">-</span></li>
                                        <li><strong>التاريخ:</strong> <span id="newDate">-</span></li>
                                        <li><strong>الكمية:</strong> <span id="newQuantite">-</span></li>
                                    </ul>
                                </div>
                            </div>
                            <div id="changesAlert" class="mt-2" style="display: none;">
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <span id="changesText"></span>
                                </small>
                            </div>
                        </div>

                        <!-- Edit Permissions Info -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-clock me-2"></i>معلومات التعديل:</h6>
                            <ul class="mb-0">
                                <li>تم إنشاء هذا الاستلام في: {{ $reception->created_at->format('d/m/Y H:i') }}</li>
                                <li>يمكن تعديل الاستلامات لمدة 7 أيام من تاريخ الإنشاء</li>
                                <li>سيتم تحديث المخزون تلقائياً بعد التعديل</li>
                                @if($reception->updated_at != $reception->created_at)
                                    <li class="text-info">آخر تعديل: {{ $reception->updated_at->format('d/m/Y H:i') }}</li>
                                @endif
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('gestionnaire.receptions.show', $reception->id_reception) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    رجوع إلى التفاصيل
                                </a>
                                <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-list me-2"></i>
                                    قائمة الاستلامات
                                </a>
                            </div>
                            
                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>
                                    إعادة تعيين
                                </button>
                                <button type="submit" class="btn btn-primary" id="saveBtn">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ التغييرات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Related Info -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات إضافية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">تأثير التعديل على المخزون:</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-1"></i>سيتم تحديث مخزون التاريخ الأصلي</li>
                                <li><i class="fas fa-check text-success me-1"></i>سيتم تحديث مخزون التاريخ الجديد (إن تغير)</li>
                                <li><i class="fas fa-check text-success me-1"></i>الحسابات ستكون دقيقة تلقائياً</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">إجراءات أخرى:</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('gestionnaire.membres.show', $reception->membre->id_membre) }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-user me-1"></i>
                                    عرض بيانات المربي
                                </a>
                                <a href="{{ route('gestionnaire.receptions.index') }}?membre_id={{ $reception->membre->id_membre }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-history me-1"></i>
                                    استلامات هذا المربي
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .form-control.changed {
        border-color: #ffc107;
        background-color: #fff3cd;
    }
    
    .text-changed {
        color: #856404 !important;
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Original values for comparison
    const originalValues = {
        id_membre: {{ $reception->id_membre }},
        membre_nom: '{{ $reception->membre->nom_complet }}',
        date_reception: '{{ $reception->date_reception->format('Y-m-d') }}',
        quantite_litres: {{ $reception->quantite_litres }}
    };

    $(document).ready(function() {
        // Initialize Select2
        $('#id_membre').select2({
            theme: 'bootstrap-5',
            placeholder: '-- ابحث عن المربي --',
            allowClear: false,
            language: {
                noResults: function() {
                    return "لم يتم العثور على نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            }
        });

        // Show membre info if selected
        if ($('#id_membre').val()) {
            showMembreInfo();
        }

        // Monitor form changes
        $('#id_membre, #date_reception, #quantite_litres').on('change input', function() {
            updateComparison();
            validateField($(this));
        });

        // Initial comparison update
        updateComparison();
    });

    function showMembreInfo() {
        const selectedOption = $('#id_membre option:selected');
        
        if (selectedOption.val()) {
            const carte = selectedOption.data('carte');
            const phone = selectedOption.data('phone');
            
            $('#membreCarte').text(carte);
            $('#membrePhone').text(phone);
            $('#membreInfo').show();
        } else {
            $('#membreInfo').hide();
        }
    }

    $('#id_membre').change(function() {
        showMembreInfo();
    });

    function updateComparison() {
        const currentValues = {
            id_membre: parseInt($('#id_membre').val()),
            membre_nom: $('#id_membre option:selected').text(),
            date_reception: $('#date_reception').val(),
            quantite_litres: parseFloat($('#quantite_litres').val())
        };

        // Update display
        $('#newMembre').text(currentValues.membre_nom || '-');
        $('#newDate').text(currentValues.date_reception ? new Date(currentValues.date_reception).toLocaleDateString('ar-MA') : '-');
        $('#newQuantite').text(currentValues.quantite_litres ? currentValues.quantite_litres.toFixed(2) + ' لتر' : '-');

        // Detect changes
        let changes = [];
        let hasChanges = false;

        if (currentValues.id_membre !== originalValues.id_membre) {
            changes.push('المربي');
            hasChanges = true;
            $('#id_membre').addClass('changed');
            $('#newMembre').addClass('text-changed');
        } else {
            $('#id_membre').removeClass('changed');
            $('#newMembre').removeClass('text-changed');
        }

        if (currentValues.date_reception !== originalValues.date_reception) {
            changes.push('تاريخ الاستلام');
            hasChanges = true;
            $('#date_reception').addClass('changed');
            $('#newDate').addClass('text-changed');
        } else {
            $('#date_reception').removeClass('changed');
            $('#newDate').removeClass('text-changed');
        }

        if (Math.abs(currentValues.quantite_litres - originalValues.quantite_litres) > 0.01) {
            changes.push('كمية اللبن');
            hasChanges = true;
            $('#quantite_litres').addClass('changed');
            $('#newQuantite').addClass('text-changed');
        } else {
            $('#quantite_litres').removeClass('changed');
            $('#newQuantite').removeClass('text-changed');
        }

        // Update changes alert
        if (hasChanges) {
            $('#changesText').text('تم تغيير: ' + changes.join('، '));
            $('#changesAlert').show();
            $('#saveBtn').removeClass('btn-primary').addClass('btn-warning').html('<i class="fas fa-save me-2"></i>حفظ التغييرات (' + changes.length + ')');
        } else {
            $('#changesAlert').hide();
            $('#saveBtn').removeClass('btn-warning').addClass('btn-primary').html('<i class="fas fa-save me-2"></i>حفظ التغييرات');
        }
    }

    function validateField(field) {
        const fieldName = field.attr('name');
        const value = field.val();

        field.removeClass('is-valid is-invalid');

        if (field.prop('required') && (!value || value.trim() === '')) {
            field.addClass('is-invalid');
            return false;
        }

        // Quantity validation
        if (fieldName === 'quantite_litres') {
            const quantite = parseFloat(value);
            if (isNaN(quantite) || quantite <= 0) {
                field.addClass('is-invalid');
                field.siblings('.invalid-feedback').text('يرجى إدخال كمية صحيحة أكبر من 0');
                return false;
            }
            if (quantite > 9999.99) {
                field.addClass('is-invalid');
                field.siblings('.invalid-feedback').text('الكمية لا يمكن أن تتجاوز 9999.99 لتر');
                return false;
            }
        }

        // Date validation
        if (fieldName === 'date_reception') {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(23, 59, 59, 999); // End of today

            if (selectedDate > today) {
                field.addClass('is-invalid');
                field.siblings('.invalid-feedback').text('لا يمكن أن يكون تاريخ الاستلام في المستقبل');
                return false;
            }
        }

        if (value) {
            field.addClass('is-valid');
        }

        return true;
    }

    function resetForm() {
        // Reset to original values
        $('#id_membre').val(originalValues.id_membre).trigger('change');
        $('#date_reception').val(originalValues.date_reception);
        $('#quantite_litres').val(originalValues.quantite_litres);

        // Clear validation and change classes
        $('input, select').removeClass('is-valid is-invalid changed');
        $('.text-changed').removeClass('text-changed');
        $('#changesAlert').hide();
        $('#saveBtn').removeClass('btn-warning').addClass('btn-primary').html('<i class="fas fa-save me-2"></i>حفظ التغييرات');

        updateComparison();
    }

    // Format quantity input
    $('#quantite_litres').on('input', function() {
        let value = parseFloat($(this).val());
        if (!isNaN(value) && value > 0) {
            validateField($(this));
        }
    });

    $('#quantite_litres').on('blur', function() {
        let value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(1));
        }
    });

    // Form submission handling
    $('form').submit(function(e) {
        // Validate all required fields
        let isValid = true;
        $('input[required], select[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('يرجى تصحيح الأخطاء في النموذج قبل الحفظ');
            return false;
        }

        // Check if there are any changes
        const hasChanges = $('#changesAlert').is(':visible');
        if (!hasChanges) {
            e.preventDefault();
            alert('لم يتم إجراء أي تغييرات على البيانات');
            return false;
        }

        $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...');
    });

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + S to save
        if (e.ctrlKey && e.keyCode === 83) {
            e.preventDefault();
            $('form').submit();
        }
        
        // Escape to reset form
        if (e.keyCode === 27) {
            resetForm();
        }
    });

    // Warn before leaving page if there are unsaved changes
    window.addEventListener('beforeunload', function(e) {
        const hasChanges = $('#changesAlert').is(':visible');
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'لديك تغييرات غير محفوظة. هل أنت متأكد من المغادرة؟';
        }
    });
</script>
@endpush