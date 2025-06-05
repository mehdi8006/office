@extends('layouts.gestionnaire')

@section('title', 'إضافة مربي جديد')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gestionnaire.membres.index') }}">إدارة المربين</a></li>
    <li class="breadcrumb-item active">إضافة مربي جديد</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        إضافة مربي جديد - {{ $cooperative->nom_cooperative }}
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('gestionnaire.membres.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        
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
                                       value="{{ old('nom_complet') }}" 
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
                                       value="{{ old('email') }}" 
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
                                       value="{{ old('telephone') }}" 
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
                                       value="{{ old('numero_carte_nationale') }}" 
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
                                          required>{{ old('adresse') }}</textarea>
                                <div class="invalid-feedback">
                                    @error('adresse')
                                        {{ $message }}
                                    @else
                                        يرجى إدخال العنوان
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>ملاحظة:</strong> سيتم تفعيل المربي تلقائياً بعد الإضافة. يمكنك تغيير حالته لاحقاً من قائمة المربين.
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>
                                رجوع إلى القائمة
                            </a>
                            
                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="fas fa-undo me-2"></i>
                                    إعادة تعيين
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ المربي
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Tips Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        نصائح مفيدة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">معلومات المربي</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-2"></i>تأكد من صحة البريد الإلكتروني</li>
                                <li><i class="fas fa-check text-success me-2"></i>تحقق من رقم الهاتف</li>
                                <li><i class="fas fa-check text-success me-2"></i>رقم البطاقة الوطنية يجب أن يكون فريد</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">بعد الإضافة</h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-2"></i>يمكن تسجيل استلامات اللبن</li>
                                <li><i class="fas fa-check text-success me-2"></i>يمكن عرض إحصائيات المربي</li>
                                <li><i class="fas fa-check text-success me-2"></i>يمكن إدارة حالة المربي</li>
                            </ul>
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
    // Format phone number as user types
    $('#telephone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        
        // Limit to 10 digits for Moroccan phone numbers
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        $(this).val(value);
    });

    // Format carte nationale as user types
    $('#numero_carte_nationale').on('input', function() {
        let value = $(this).val().toUpperCase();
        $(this).val(value);
    });

    // Real-time validation feedback
    $('input, textarea').on('blur', function() {
        const field = $(this);
        const value = field.val().trim();
        
        if (field.prop('required') && !value) {
            field.addClass('is-invalid');
        } else {
            field.removeClass('is-invalid');
            
            // Email validation
            if (field.attr('type') === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    field.addClass('is-invalid');
                } else {
                    field.addClass('is-valid');
                }
            }
            
            // Phone validation (Moroccan format)
            if (field.attr('name') === 'telephone' && value) {
                const phoneRegex = /^0[5-7][0-9]{8}$/;
                if (!phoneRegex.test(value)) {
                    field.addClass('is-invalid');
                    field.siblings('.invalid-feedback').text('رقم الهاتف يجب أن يكون في الشكل: 0612345678');
                } else {
                    field.addClass('is-valid');
                }
            }
            
            // Other fields
            if (field.attr('type') !== 'email' && field.attr('name') !== 'telephone' && value) {
                field.addClass('is-valid');
            }
        }
    });

    // Check for duplicate email and carte nationale
    let emailTimeout, carteTimeout;

    $('#email').on('input', function() {
        const email = $(this).val();
        const field = $(this);
        
        clearTimeout(emailTimeout);
        
        if (email.length > 5) {
            emailTimeout = setTimeout(function() {
                // Here you could add AJAX call to check for duplicates
                console.log('Checking email:', email);
            }, 500);
        }
    });

    $('#numero_carte_nationale').on('input', function() {
        const carte = $(this).val();
        const field = $(this);
        
        clearTimeout(carteTimeout);
        
        if (carte.length > 3) {
            carteTimeout = setTimeout(function() {
                // Here you could add AJAX call to check for duplicates
                console.log('Checking carte:', carte);
            }, 500);
        }
    });

    // Auto-generate email suggestion based on name
    $('#nom_complet').on('blur', function() {
        const nom = $(this).val().trim();
        const emailField = $('#email');
        
        if (nom && !emailField.val()) {
            const suggestion = nom.toLowerCase()
                                  .replace(/\s+/g, '.')
                                  .replace(/[^a-z.]/g, '') + '@gmail.com';
            emailField.attr('placeholder', 'مثال: ' + suggestion);
        }
    });
</script>
@endpush