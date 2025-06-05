@extends('layouts.gestionnaire')

@section('title', 'إنشاء تسليم جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.stock.index') }}">إدارة المخزون</a></li>
    <li class="breadcrumb-item active">إنشاء تسليم جديد</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Stock Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-warehouse me-2"></i>
                        معلومات المخزون المتاح
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3 bg-light">
                                <h4 class="text-primary mb-1">{{ number_format($stock->quantite_totale, 1) }}</h4>
                                <small class="text-muted">إجمالي المخزون (لتر)</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3 bg-success text-white">
                                <h4 class="mb-1">{{ number_format($stock->quantite_disponible, 1) }}</h4>
                                <small>المتاح للتسليم (لتر)</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3 bg-light">
                                <h4 class="text-info mb-1">{{ number_format($stock->quantite_livree, 1) }}</h4>
                                <small class="text-muted">تم تسليمه (لتر)</small>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="border rounded p-3 bg-light">
                                <h4 class="text-warning mb-1">{{ $stock->date_stock->format('d/m/Y') }}</h4>
                                <small class="text-muted">تاريخ المخزون</small>
                            </div>
                        </div>
                    </div>

                    @if($stock->quantite_disponible <= 0)
                        <div class="alert alert-warning mt-3 text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h5>لا يوجد مخزون متاح للتسليم</h5>
                            <p class="mb-0">تم تسليم كامل المخزون لهذا اليوم</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($stock->quantite_disponible > 0)
                <!-- Livraison Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>
                            إنشاء تسليم للمصنع - {{ $cooperative->nom_cooperative }}
                        </h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('gestionnaire.stock.store-livraison') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="row">
                                <!-- Date Livraison -->
                                <div class="col-md-6 mb-3">
                                    <label for="date_livraison" class="form-label">
                                        تاريخ التسليم <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('date_livraison') is-invalid @enderror" 
                                           id="date_livraison" 
                                           name="date_livraison" 
                                           value="{{ old('date_livraison', $date) }}" 
                                           readonly
                                           required>
                                    <div class="invalid-feedback">
                                        @error('date_livraison')
                                            {{ $message }}
                                        @else
                                            يرجى اختيار تاريخ التسليم
                                        @enderror
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        يجب أن يكون التسليم من مخزون نفس اليوم
                                    </div>
                                </div>

                                <!-- Prix Unitaire -->
                                <div class="col-md-6 mb-3">
                                    <label for="prix_unitaire" class="form-label">
                                        السعر لكل لتر (درهم) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('prix_unitaire') is-invalid @enderror" 
                                               id="prix_unitaire" 
                                               name="prix_unitaire" 
                                               value="{{ old('prix_unitaire', '2.50') }}" 
                                               min="0.01" 
                                               max="100" 
                                               step="0.01" 
                                               placeholder="2.50"
                                               required>
                                        <span class="input-group-text">درهم/لتر</span>
                                        <div class="invalid-feedback">
                                            @error('prix_unitaire')
                                                {{ $message }}
                                            @else
                                                يرجى إدخال سعر صحيح
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        السعر الاعتيادي: 2.50 درهم/لتر
                                    </div>
                                </div>

                                <!-- Quantite -->
                                <div class="col-md-12 mb-3">
                                    <label for="quantite_litres" class="form-label">
                                        كمية التسليم (بالليتر) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('quantite_litres') is-invalid @enderror" 
                                               id="quantite_litres" 
                                               name="quantite_litres" 
                                               value="{{ old('quantite_litres') }}" 
                                               min="0.1" 
                                               max="{{ $stock->quantite_disponible }}" 
                                               step="0.1" 
                                               placeholder="0.0"
                                               required>
                                        <span class="input-group-text">لتر</span>
                                        <button class="btn btn-outline-secondary" type="button" onclick="setMaxQuantity()">
                                            كامل المخزون
                                        </button>
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
                                        الحد الأقصى المتاح: {{ number_format($stock->quantite_disponible, 1) }} لتر
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Quantity Buttons -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">كميات سريعة:</label>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="setQuantity({{ $stock->quantite_disponible * 0.25 }})">
                                            25% ({{ number_format($stock->quantite_disponible * 0.25, 1) }} لتر)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="setQuantity({{ $stock->quantite_disponible * 0.5 }})">
                                            50% ({{ number_format($stock->quantite_disponible * 0.5, 1) }} لتر)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="setQuantity({{ $stock->quantite_disponible * 0.75 }})">
                                            75% ({{ number_format($stock->quantite_disponible * 0.75, 1) }} لتر)
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="setQuantity({{ $stock->quantite_disponible }})">
                                            100% ({{ number_format($stock->quantite_disponible, 1) }} لتر)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Box -->
                            <div class="alert alert-info" id="previewBox" style="display: none;">
                                <h6><i class="fas fa-calculator me-2"></i>ملخص التسليم:</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>الكمية:</strong> <span id="previewQuantite"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>السعر:</strong> <span id="previewPrix"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>المبلغ الإجمالي:</strong> <span id="previewMontant" class="text-success fw-bold"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>المتبقي:</strong> <span id="previewRestant"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>ملاحظات مهمة:</h6>
                                <ul class="mb-0">
                                    <li>سيتم خصم الكمية المُسلّمة من المخزون المتاح تلقائياً</li>
                                    <li>يمكن إلغاء التسليم فقط إذا كان في حالة "مخطط"</li>
                                    <li>سيتم حساب المبلغ الإجمالي تلقائياً</li>
                                    <li>تأكد من صحة السعر قبل التأكيد</li>
                                </ul>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    رجوع إلى المخزون
                                </a>
                                
                                <div>
                                    <button type="reset" class="btn btn-outline-warning me-2" onclick="resetForm()">
                                        <i class="fas fa-undo me-2"></i>
                                        إعادة تعيين
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <i class="fas fa-truck me-2"></i>
                                        إنشاء التسليم
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- No Stock Available -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">لا يوجد مخزون متاح للتسليم</h4>
                        <p class="text-muted">تم تسليم كامل مخزون هذا اليوم أو لا يوجد مخزون أصلاً</p>
                        
                        <div class="mt-4">
                            <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-primary me-2">
                                <i class="fas fa-warehouse me-1"></i>
                                العودة إلى المخزون
                            </a>
                            <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>
                                تسجيل استلام جديد
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Livraisons -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر التسليمات
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recentLivraisons">
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

@push('scripts')
<script>
    const maxQuantite = {{ $stock->quantite_disponible }};

    $(document).ready(function() {
        // Load recent livraisons
        loadRecentLivraisons();
        
        // Monitor form changes for preview
        $('#quantite_litres, #prix_unitaire').on('input', updatePreview);
        
        // Initial preview
        updatePreview();
        
        // Format numbers
        $('#prix_unitaire').on('blur', function() {
            const value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value.toFixed(2));
            }
        });
        
        $('#quantite_litres').on('blur', function() {
            const value = parseFloat($(this).val());
            if (!isNaN(value)) {
                $(this).val(value.toFixed(1));
            }
        });
    });

    function setQuantity(quantity) {
        const roundedQuantity = Math.round(quantity * 10) / 10; // Round to 1 decimal
        $('#quantite_litres').val(roundedQuantity.toFixed(1));
        updatePreview();
        validateQuantity();
    }

    function setMaxQuantity() {
        setQuantity(maxQuantite);
    }

    function validateQuantity() {
        const quantite = parseFloat($('#quantite_litres').val());
        const field = $('#quantite_litres');
        
        field.removeClass('is-valid is-invalid');
        
        if (isNaN(quantite) || quantite <= 0) {
            field.addClass('is-invalid');
            field.siblings('.invalid-feedback').text('يرجى إدخال كمية صحيحة');
            return false;
        }
        
        if (quantite > maxQuantite) {
            field.addClass('is-invalid');
            field.siblings('.invalid-feedback').text(`الكمية تتجاوز المخزون المتاح (${maxQuantite.toFixed(1)} لتر)`);
            return false;
        }
        
        field.addClass('is-valid');
        return true;
    }

    function validatePrice() {
        const prix = parseFloat($('#prix_unitaire').val());
        const field = $('#prix_unitaire');
        
        field.removeClass('is-valid is-invalid');
        
        if (isNaN(prix) || prix <= 0) {
            field.addClass('is-invalid');
            field.siblings('.invalid-feedback').text('يرجى إدخال سعر صحيح');
            return false;
        }
        
        if (prix > 100) {
            field.addClass('is-invalid');
            field.siblings('.invalid-feedback').text('السعر مرتفع جداً - تأكد من الرقم');
            return false;
        }
        
        field.addClass('is-valid');
        return true;
    }

    function updatePreview() {
        const quantite = parseFloat($('#quantite_litres').val());
        const prix = parseFloat($('#prix_unitaire').val());
        
        if (!isNaN(quantite) && !isNaN(prix) && quantite > 0 && prix > 0) {
            const montantTotal = quantite * prix;
            const restant = maxQuantite - quantite;
            
            $('#previewQuantite').text(quantite.toFixed(1) + ' لتر');
            $('#previewPrix').text(prix.toFixed(2) + ' درهم/لتر');
            $('#previewMontant').text(montantTotal.toFixed(2) + ' درهم');
            $('#previewRestant').text(restant.toFixed(1) + ' لتر');
            
            $('#previewBox').show();
        } else {
            $('#previewBox').hide();
        }
    }

    function resetForm() {
        $('form')[0].reset();
        $('#prix_unitaire').val('2.50');
        $('input').removeClass('is-valid is-invalid');
        $('#previewBox').hide();
    }

    function loadRecentLivraisons() {
        $.ajax({
            url: '{{ route("gestionnaire.stock.livraisons") }}',
            method: 'GET',
            data: { ajax: 1, limit: 5 },
            success: function(response) {
                // This would need to be implemented to return JSON
                // For now, show a simple message
                $('#recentLivraisons').html(`
                    <div class="text-center text-muted">
                        <p>لعرض آخر التسليمات، اذهب إلى <a href="{{ route('gestionnaire.stock.livraisons') }}">صفحة التسليمات</a></p>
                    </div>
                `);
            },
            error: function() {
                $('#recentLivraisons').html('<p class="text-center text-danger">خطأ في تحميل البيانات</p>');
            }
        });
    }

    // Form validation
    $('#quantite_litres').on('input blur', validateQuantity);
    $('#prix_unitaire').on('input blur', validatePrice);

    // Form submission
    $('form').submit(function(e) {
        // Validate all fields
        const isQuantityValid = validateQuantity();
        const isPriceValid = validatePrice();
        
        if (!isQuantityValid || !isPriceValid) {
            e.preventDefault();
            alert('يرجى تصحيح الأخطاء في النموذج قبل المتابعة');
            return false;
        }
        
        // Confirm submission
        const quantite = parseFloat($('#quantite_litres').val());
        const prix = parseFloat($('#prix_unitaire').val());
        const montant = quantite * prix;
        
        if (!confirm(`هل أنت متأكد من إنشاء هذا التسليم؟\n\nالكمية: ${quantite.toFixed(1)} لتر\nالسعر: ${prix.toFixed(2)} درهم/لتر\nالمبلغ الإجمالي: ${montant.toFixed(2)} درهم`)) {
            e.preventDefault();
            return false;
        }
        
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>جاري الإنشاء...');
    });

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + Enter to submit
        if (e.ctrlKey && e.keyCode === 13) {
            $('form').submit();
        }
        
        // F1 for max quantity
        if (e.keyCode === 112) {
            e.preventDefault();
            setMaxQuantity();
        }
        
        // F2 for half quantity
        if (e.keyCode === 113) {
            e.preventDefault();
            setQuantity(maxQuantite * 0.5);
        }
    });

    // Focus on quantity field
    $('#quantite_litres').focus();
</script>
@endpush

@push('styles')
<style>
    .btn-group .btn {
        font-size: 0.875rem;
    }
    
    .alert-info h6 {
        color: #0c5460;
    }
    
    #previewMontant {
        font-size: 1.1rem;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        border-color: #28a745;
    }
    
    .input-group .btn {
        z-index: 1;
    }
</style>
@endpush