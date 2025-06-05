@extends('layouts.gestionnaire')

@section('title', 'تسجيل استلام جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.receptions.index') }}">إدارة الاستلام</a></li>
    <li class="breadcrumb-item active">تسجيل استلام جديد</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        تسجيل استلام جديد - {{ $cooperative->nom_cooperative }}
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('gestionnaire.receptions.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="row">
                            <!-- Membre Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="id_membre" class="form-label">
                                    اختر المربي <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('id_membre') is-invalid @enderror" 
                                        id="id_membre" 
                                        name="id_membre" 
                                        required>
                                    <option value="">-- اختر المربي --</option>
                                    @foreach($membres as $membre)
                                        <option value="{{ $membre->id_membre }}" 
                                                {{ old('id_membre', request('membre')) == $membre->id_membre ? 'selected' : '' }}
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
                                       value="{{ old('date_reception', today()->format('Y-m-d')) }}" 
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
                                           value="{{ old('quantite_litres') }}" 
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

                        <!-- Preview Box -->
                        <div class="alert alert-info" id="previewBox" style="display: none;">
                            <h6><i class="fas fa-eye me-2"></i>معاينة الاستلام:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>المربي:</strong> <span id="previewMembre"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>التاريخ:</strong> <span id="previewDate"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>الكمية:</strong> <span id="previewQuantite"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>ملاحظات مهمة:</h6>
                            <ul class="mb-0">
                                <li>تأكد من صحة كمية اللبن المستلمة</li>
                                <li>يمكن تعديل الاستلام خلال 7 أيام من تاريخ التسجيل</li>
                                <li>سيتم تحديث المخزون تلقائياً بعد التسجيل</li>
                                <li>سيتم إنشاء رقم استلام تلقائياً</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>
                                رجوع إلى القائمة
                            </a>
                            
                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="fas fa-undo me-2"></i>
                                    إعادة تعيين
                                </button>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>
                                    تسجيل الاستلام
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Access Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        وصول سريع
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ today()->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}" 
                               class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-calendar-day me-1"></i>
                                استلامات اليوم
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('gestionnaire.membres.index') }}" 
                               class="btn btn-outline-info btn-sm w-100">
                                <i class="fas fa-users me-1"></i>
                                قائمة المربين
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('gestionnaire.stock.index') }}" 
                               class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-warehouse me-1"></i>
                                المخزون
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Receptions -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر الاستلامات (اليوم)
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recentReceptions">
                        <!-- Will be loaded via AJAX -->
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
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
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for membre selection
    $('#id_membre').select2({
        theme: 'bootstrap-5',
        placeholder: '-- ابحث عن المربي --',
        allowClear: true,
        language: {
            noResults: function() {
                return "لم يتم العثور على نتائج";
            },
            searching: function() {
                return "جاري البحث...";
            }
        }
    });

    // Show membre info when selected
    $('#id_membre').change(function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            const carte = selectedOption.data('carte');
            const phone = selectedOption.data('phone');
            
            $('#membreCarte').text(carte);
            $('#membrePhone').text(phone);
            $('#membreInfo').show();
            
            updatePreview();
        } else {
            $('#membreInfo').hide();
            $('#previewBox').hide();
        }
    });

    // Update preview on form changes
    $('#date_reception, #quantite_litres').on('input change', updatePreview);

    function updatePreview() {
        const membre = $('#id_membre option:selected').text();
        const date = $('#date_reception').val();
        const quantite = $('#quantite_litres').val();
        
        if (membre && date && quantite) {
            $('#previewMembre').text(membre);
            $('#previewDate').text(new Date(date).toLocaleDateString('ar-MA'));
            $('#previewQuantite').text(parseFloat(quantite).toFixed(1) + ' لتر');
            $('#previewBox').show();
        } else {
            $('#previewBox').hide();
        }
    }

    // Format quantity input
    $('#quantite_litres').on('input', function() {
        let value = parseFloat($(this).val());
        
        if (!isNaN(value)) {
            // Validate range
            if (value < 0.01) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('الكمية يجب أن تكون أكبر من 0');
            } else if (value > 9999.99) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').text('الكمية لا يمكن أن تتجاوز 9999.99 لتر');
            } else {
                $(this).removeClass('is-invalid').addClass('is-valid');
            }
        }
    });

    // Load recent receptions
    loadRecentReceptions();

    function loadRecentReceptions() {
        $.ajax({
            url: '{{ route("gestionnaire.receptions.daily-summary") }}',
            method: 'GET',
            data: { date: '{{ today()->format("Y-m-d") }}' },
            success: function(response) {
                const receptions = response.receptions;
                let html = '';
                
                if (receptions.length > 0) {
                    html = '<div class="table-responsive"><table class="table table-sm"><tbody>';
                    
                    receptions.slice(0, 5).forEach(function(reception) {
                        html += `
                            <tr>
                                <td>
                                    <strong>${reception.matricule_reception}</strong><br>
                                    <small class="text-muted">${reception.membre.nom_complet}</small>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success">${parseFloat(reception.quantite_litres).toFixed(1)} لتر</span><br>
                                    <small class="text-muted">${new Date(reception.created_at).toLocaleTimeString('ar-MA')}</small>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table></div>';
                    
                    if (receptions.length > 5) {
                        html += `<div class="text-center">
                            <a href="{{ route('gestionnaire.receptions.index') }}?date_from={{ today()->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}" 
                               class="btn btn-sm btn-outline-primary">
                                عرض جميع استلامات اليوم (${receptions.length})
                            </a>
                        </div>`;
                    }
                } else {
                    html = '<p class="text-center text-muted">لا توجد استلامات مسجلة اليوم</p>';
                }
                
                $('#recentReceptions').html(html);
            },
            error: function() {
                $('#recentReceptions').html('<p class="text-center text-danger">خطأ في تحميل البيانات</p>');
            }
        });
    }

    // Form submission handling
    $('form').submit(function(e) {
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>جاري التسجيل...');
    });

    // Auto-refresh recent receptions every 30 seconds
    setInterval(loadRecentReceptions, 30000);

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + Enter to submit
        if (e.ctrlKey && e.keyCode === 13) {
            $('form').submit();
        }
        
        // Escape to clear form
        if (e.keyCode === 27) {
            $('form')[0].reset();
            $('#id_membre').val(null).trigger('change');
            $('#previewBox').hide();
            $('#membreInfo').hide();
        }
    });

    // Set focus on membre select
    $('#id_membre').focus();
    
    // If membre is pre-selected (from URL), trigger change
    if ($('#id_membre').val()) {
        $('#id_membre').trigger('change');
    }
});
</script>
@endpush