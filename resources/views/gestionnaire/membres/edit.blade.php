@extends('layouts.gestionnaire')

@section('title', 'تعديل المربي - ' . $membre->nom_complet)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.membres.index') }}">إدارة المربين</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.membres.show', $membre->id_membre) }}">{{ $membre->nom_complet }}</a></li>
    <li class="breadcrumb-item active">تعديل</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Member Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                            <span style="font-size: 1.5rem; font-weight: bold;">
                                {{ substr($membre->nom_complet, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $membre->nom_complet }}</h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-id-card me-1"></i>
                                {{ $membre->numero_carte_nationale }}
                                <span class="badge bg-{{ $membre->statut_color }} ms-2">{{ $membre->statut_label }}</span>
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
                        تعديل بيانات المربي
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('gestionnaire.membres.update', $membre->id_membre) }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Nom complet -->
                            <div class="col-md-6 mb-3">
                                <label for="nom_complet" class="form-label">
                                    الاسم الكامل <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nom_complet') is-invalid @enderror" 
                                       id="nom_complet" 
                                       name="nom_complet" 
                                       value="{{ old('nom_complet', $membre->nom_complet) }}" 
                                       required>
                                <div class="invalid-feedback">
                                    @error('nom_complet')
                                        {{ $message }}
                                    @else
                                        يرجى إدخال الاسم الكامل
                                    @enderror
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    البريد الإلكتروني <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $membre->email) }}" 
                                       required>
                                <div class="invalid-feedback">
                                    @error('email')
                                        {{ $message }}
                                    @else
                                        يرجى إدخال بريد إلكتروني صحيح
                                    @enderror
                                </div>
                            </div>

                            <!-- Telephone -->
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">
                                    رقم الهاتف <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" 
                                       name="telephone" 
                                       value="{{ old('telephone', $membre->telephone) }}" 
                                       placeholder="0612345678"
                                       required>
                                <div class="invalid-feedback">
                                    @error('telephone')
                                        {{ $message }}
                                    @else
                                        يرجى إدخال رقم الهاتف
                                    @enderror
                                </div>
                            </div>

                            <!-- Numero carte nationale -->
                            <div class="col-md-6 mb-3">
                                <label for="numero_carte_nationale" class="form-label">
                                    رقم البطاقة الوطنية <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('numero_carte_nationale') is-invalid @enderror" 
                                       id="numero_carte_nationale" 
                                       name="numero_carte_nationale" 
                                       value="{{ old('numero_carte_nationale', $membre->numero_carte_nationale) }}" 
                                       placeholder="AB123456"
                                       required>
                                <div class="invalid-feedback">
                                    @error('numero_carte_nationale')
                                        {{ $message }}
                                    @else
                                        يرجى إدخال رقم البطاقة الوطنية
                                    @enderror
                                </div>
                            </div>

                            <!-- Adresse -->
                            <div class="col-12 mb-3">
                                <label for="adresse" class="form-label">
                                    العنوان <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                          id="adresse" 
                                          name="adresse" 
                                          rows="3" 
                                          placeholder="العنوان الكامل للمربي..."
                                          required>{{ old('adresse', $membre->adresse) }}</textarea>
                                <div class="invalid-feedback">
                                    @error('adresse')
                                        {{ $message }}
                                    @else
                                        يرجى إدخال العنوان
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Change Summary -->
                        <div class="alert alert-info" id="changeSummary" style="display: none;">
                            <h6><i class="fas fa-info-circle me-2"></i>التغييرات المكتشفة:</h6>
                            <ul id="changesList" class="mb-0"></ul>
                        </div>

                        <!-- Member Info -->
                        <div class="alert alert-light">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>تاريخ التسجيل:</strong><br>
                                    <small class="text-muted">{{ $membre->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>آخر تحديث:</strong><br>
                                    <small class="text-muted">{{ $membre->updated_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>الحالة الحالية:</strong><br>
                                    <span class="badge bg-{{ $membre->statut_color }}">{{ $membre->statut_label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('gestionnaire.membres.show', $membre->id_membre) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    رجوع إلى التفاصيل
                                </a>
                                <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-list me-2"></i>
                                    قائمة المربين
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

            <!-- Quick Actions Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        إجراءات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-info btn-sm w-100" 
                                    onclick="changeStatus({{ $membre->id_membre }}, '{{ $membre->statut }}')">
                                <i class="fas fa-exchange-alt me-1"></i>
                                تغيير الحالة
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('gestionnaire.receptions.create') }}?membre={{ $membre->id_membre }}" 
                               class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-plus me-1"></i>
                                استلام جديد
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('gestionnaire.receptions.index') }}?membre_id={{ $membre->id_membre }}" 
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-history me-1"></i>
                                سجل الاستلامات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تغيير حالة المربي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الحالة الجديدة</label>
                        <select class="form-select" name="statut" id="statusSelect" required>
                            <option value="">اختر الحالة</option>
                            <option value="actif">نشط</option>
                            <option value="inactif">غير نشط</option>
                            <option value="suppression">محذوف</option>
                        </select>
                    </div>
                    <div class="mb-3" id="reasonDiv" style="display: none;">
                        <label class="form-label">سبب الحذف</label>
                        <textarea class="form-control" name="raison_suppression" rows="3" 
                                  placeholder="اذكر سبب حذف المربي..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-lg {
        width: 60px;
        height: 60px;
    }
    
    .form-control.is-valid,
    .form-control.is-invalid {
        background-position: left 0.75rem center;
    }
    
    .change-highlight {
        background-color: #fff3cd !important;
        border-color: #ffc107 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Store original values for change detection
    const originalValues = {
        nom_complet: '{{ $membre->nom_complet }}',
        email: '{{ $membre->email }}',
        telephone: '{{ $membre->telephone }}',
        numero_carte_nationale: '{{ $membre->numero_carte_nationale }}',
        adresse: '{{ $membre->adresse }}'
    };

    let currentMembreId = {{ $membre->id_membre }};

    $(document).ready(function() {
        // Monitor form changes
        $('input, textarea').on('input', function() {
            detectChanges();
            validateField($(this));
        });

        // Initial validation
        $('input, textarea').each(function() {
            validateField($(this));
        });
    });

    function detectChanges() {
        const changes = [];
        let hasChanges = false;

        // Check each field for changes
        Object.keys(originalValues).forEach(function(field) {
            const currentValue = $(`[name="${field}"]`).val().trim();
            const originalValue = originalValues[field];

            if (currentValue !== originalValue) {
                hasChanges = true;
                changes.push({
                    field: field,
                    original: originalValue,
                    current: currentValue
                });

                // Highlight changed field
                $(`[name="${field}"]`).addClass('change-highlight');
            } else {
                $(`[name="${field}"]`).removeClass('change-highlight');
            }
        });

        // Update change summary
        if (hasChanges) {
            let changesList = '';
            const fieldNames = {
                'nom_complet': 'الاسم الكامل',
                'email': 'البريد الإلكتروني',
                'telephone': 'الهاتف',
                'numero_carte_nationale': 'رقم البطاقة الوطنية',
                'adresse': 'العنوان'
            };

            changes.forEach(function(change) {
                changesList += `<li><strong>${fieldNames[change.field]}:</strong> تم تغييره</li>`;
            });

            $('#changesList').html(changesList);
            $('#changeSummary').show();
            $('#saveBtn').removeClass('btn-primary').addClass('btn-warning').html('<i class="fas fa-save me-2"></i>حفظ التغييرات (' + changes.length + ')');
        } else {
            $('#changeSummary').hide();
            $('#saveBtn').removeClass('btn-warning').addClass('btn-primary').html('<i class="fas fa-save me-2"></i>حفظ التغييرات');
        }
    }

    function validateField(field) {
        const fieldName = field.attr('name');
        const value = field.val().trim();

        field.removeClass('is-valid is-invalid');

        if (field.prop('required') && !value) {
            field.addClass('is-invalid');
            return false;
        }

        // Email validation
        if (fieldName === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                field.addClass('is-invalid');
                return false;
            }
        }

        // Phone validation
        if (fieldName === 'telephone' && value) {
            const phoneRegex = /^0[5-7][0-9]{8}$/;
            if (!phoneRegex.test(value)) {
                field.addClass('is-invalid');
                field.siblings('.invalid-feedback').text('رقم الهاتف يجب أن يكون في الشكل: 0612345678');
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
        Object.keys(originalValues).forEach(function(field) {
            $(`[name="${field}"]`).val(originalValues[field]);
        });

        // Clear validation classes
        $('input, textarea').removeClass('is-valid is-invalid change-highlight');
        $('#changeSummary').hide();
        $('#saveBtn').removeClass('btn-warning').addClass('btn-primary').html('<i class="fas fa-save me-2"></i>حفظ التغييرات');
    }

    // Format phone number
    $('#telephone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        $(this).val(value);
    });

    // Format carte nationale
    $('#numero_carte_nationale').on('input', function() {
        let value = $(this).val().toUpperCase();
        $(this).val(value);
    });

    // Status change functionality
    function changeStatus(membreId, currentStatus) {
        // Reset form
        $('#statusForm')[0].reset();
        $('#reasonDiv').hide();
        
        // Disable current status option
        $('#statusSelect option').prop('disabled', false);
        $('#statusSelect option[value="' + currentStatus + '"]').prop('disabled', true);
        
        $('#statusModal').modal('show');
    }

    // Show/hide reason textarea
    $('#statusSelect').change(function() {
        if ($(this).val() === 'suppression') {
            $('#reasonDiv').show();
            $('textarea[name="raison_suppression"]').prop('required', true);
        } else {
            $('#reasonDiv').hide();
            $('textarea[name="raison_suppression"]').prop('required', false);
        }
    });

    // Handle status form submission
    $('#statusForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        showLoading();
        
        $.ajax({
            url: `/gestionnaire/membres/${currentMembreId}/status`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                $('#statusModal').modal('hide');
                
                if (response.success) {
                    alert(response.success);
                    location.reload();
                }
            },
            error: function(xhr) {
                hideLoading();
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert('خطأ: ' + xhr.responseJSON.message);
                } else {
                    alert('حدث خطأ أثناء تغيير الحالة');
                }
            }
        });
    });

    // Form submission handling
    $('form').submit(function(e) {
        // Validate all fields before submission
        let isValid = true;
        $('input[required], textarea[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('يرجى تصحيح الأخطاء في النموذج قبل الحفظ');
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
        const hasChanges = $('#changeSummary').is(':visible');
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'لديك تغييرات غير محفوظة. هل أنت متأكد من المغادرة؟';
        }
    });
</script>
@endpush