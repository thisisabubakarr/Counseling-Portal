@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
    <div class="wizard-card">
        <div class="wizard-card-header">
            <h2>Book an Appointment</h2>
            <a href="{{ route('faculty.dashboard') }}" class="wizard-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
        </div>

        @if ($hasActive)
            <div class="status-banner error">You already have an active appointment. You cannot book another session until it is completed, rejected, or cancelled.</div>
        @else
            <ul class="wizard-steps" id="wizardSteps">
                <li class="wizard-step-item" data-step-indicator="1">
                    <span class="wizard-step-num">1</span>
                    <span class="wizard-step-label">Consent</span>
                </li>
                <li class="wizard-step-item" data-step-indicator="2">
                    <span class="wizard-step-num">2</span>
                    <span class="wizard-step-label">Counselor &amp; Slot</span>
                </li>
                <li class="wizard-step-item" data-step-indicator="3">
                    <span class="wizard-step-num">3</span>
                    <span class="wizard-step-label">Presenting Concerns</span>
                </li>
            </ul>

            <form method="POST" action="{{ route('faculty.appointments.store') }}" enctype="multipart/form-data" id="bookingForm" novalidate>
                @csrf

                {{-- Step 1: Consent --}}
                <div class="wizard-step" data-step="1">
                    <div class="consent-document">
                        @if ($consentPolicy)
                            <h4>{{ $consentPolicy->title }}</h4>
                            <div class="policy-body">{!! nl2br(e($consentPolicy->body)) !!}</div>
                        @else
                            <p class="muted">No consent document is currently published. You must still confirm below to continue.</p>
                        @endif
                        @include('faculty.partials.consent-acknowledgement', ['facultyStaff' => $facultyStaff])
                    </div>

                    <div class="consent-confirmation-box">
                        <h5>Consent Confirmation</h5>
                        <label class="checkbox-row">
                            <input type="checkbox" name="consent" value="1" id="consentCheck" {{ old('consent') ? 'checked' : '' }}>
                            <strong>I accept this</strong>
                        </label>
                        <p class="muted">Press the box above to place a tick. You must check this box to agree to the terms.</p>
                        <div class="consent-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>If the box is not ticked, you will not be able to proceed to book an appointment.</span>
                        </div>
                        <p class="field-error" id="consentError" style="display:none">Please tick the box above before continuing.</p>
                        @error('consent')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Step 2: Counselor & Slot --}}
                <div class="wizard-step" data-step="2">
                    <div class="home-section">
                        <h3>Counselor &amp; Time</h3>
                        <div class="detail-grid">
                            <div class="form-group">
                                <label for="counselor_id">Counselor <span class="required">*</span></label>
                                <select id="counselor_id" name="counselor_id">
                                    <option value="">Select a counselor</option>
                                    @foreach ($counselors as $counselor)
                                        <option value="{{ $counselor->id }}" {{ old('counselor_id') == $counselor->id ? 'selected' : '' }}>{{ $counselor->name }}</option>
                                    @endforeach
                                </select>
                                @error('counselor_id')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="date">Date <span class="required">*</span></label>
                                <input type="date" id="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" max="{{ now()->addDays(14)->toDateString() }}">
                                <p class="field-error" id="dateWeekendError" style="display:none">Weekends are not available. Please choose a weekday (Mon–Fri).</p>
                                @error('date')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Available Slots <span class="required">*</span></label>
                            <div class="slot-container" id="slotContainer">
                                <p class="no-slots">Select a counselor and date to see available slots.</p>
                            </div>
                            @error('slot')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="home-section" style="margin-top:1rem">
                        <h3>Additional Information</h3>
                        <div class="form-group">
                            <label for="mental_health_questions">Anything you'd like the counselor to know? <span class="optional-tag">(optional)</span></label>
                            <textarea id="mental_health_questions" name="mental_health_questions" rows="4">{{ old('mental_health_questions') }}</textarea>
                        </div>
                        <div class="detail-grid">
                            <div class="form-group">
                                <label for="emergency_contact_name">Emergency Contact Name <span class="required">*</span></label>
                                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
                                @error('emergency_contact_name')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="emergency_contact_phone">Emergency Contact Phone <span class="required">*</span></label>
                                <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">
                                @error('emergency_contact_phone')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes <span class="optional-tag">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Presenting Concerns --}}
                <div class="wizard-step" data-step="3">
                    <div class="home-section">
                        <h3>Presenting Concerns</h3>
                        <div class="concerns-grid">
                            @foreach ($concerns = [
                                'Work-Related Stress' => ['Workload management', 'Time management', 'Underconfidence', 'Lack of motivation', 'Performance anxiety', 'Procrastination', 'Perfectionism', 'Imposter syndrome', 'Burnout/fatigue'],
                                'Self-Concept' => ['Stress / anxiety', 'Self-image', 'Existential concerns', 'Loneliness/alienation', 'Eating concerns', 'Self-harm', 'Sensitivity to criticism'],
                                'Social & Interpersonal' => ['Workplace conflict', 'Relationship grief', 'Harassment / abuse', 'Distress from conflicts with colleagues', 'Social alienation or exclusion'],
                                'Health, Finances & Transitions' => ['Physical health', 'Sleep habits', 'Career / financial stress', 'Life transitions', 'Adjustment to a new role'],
                                'Grief & Collective Distress' => ['Personal loss / grief', 'Global / collective grief'],
                                'Risk & Safety' => ['Suicidal thoughts', 'Thoughts of harming others', 'Substance use'],
                            ] as $category => $items)
                                <div class="concern-group">
                                    <h5>{{ $category }}</h5>
                                    @foreach ($items as $item)
                                        <label class="checkbox-row">
                                            <input type="checkbox" name="presenting_concerns[]" value="{{ $item }}" class="concern-checkbox" data-suicidal="{{ $item === 'Suicidal thoughts' ? '1' : '0' }}">
                                            {{ $item }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div id="safetyMessage" class="safety-message hidden">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            If you are in immediate danger, please contact emergency services or the campus emergency line right away. A counselor will also be alerted urgently.
                        </div>
                        <input type="hidden" name="suicidal_thoughts" id="suicidalThoughtsInput" value="0">
                    </div>
                </div>

                <div class="wizard-nav">
                    <a href="{{ route('faculty.dashboard') }}" class="btn btn-secondary" id="cancelBtn">Cancel</a>
                    <button type="button" class="btn btn-secondary" id="backBtn" style="display:none">Back</button>
                    <span class="spacer"></span>
                    <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="display:none">Submit Request</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    const wizardForm = document.getElementById('bookingForm');
@if ($hasActive)
@else
    const steps = Array.from(document.querySelectorAll('.wizard-step'));
    const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
    const cancelBtn = document.getElementById('cancelBtn');
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const totalSteps = steps.length;
    let currentStep = @if ($errors->hasAny(['counselor_id', 'date', 'slot', 'emergency_contact_name', 'emergency_contact_phone', 'mental_health_questions', 'notes'])) 2 @elseif ($errors->has('presenting_concerns')) 3 @else 1 @endif;

    function showStep(step) {
        steps.forEach((el) => {
            el.style.display = Number(el.dataset.step) === step ? '' : 'none';
        });
        indicators.forEach((el) => {
            const n = Number(el.dataset.stepIndicator);
            el.classList.toggle('active', n === step);
            el.classList.toggle('completed', n < step);
        });
        cancelBtn.style.display = step === 1 ? '' : 'none';
        backBtn.style.display = step > 1 ? '' : 'none';
        nextBtn.style.display = step < totalSteps ? '' : 'none';
        submitBtn.style.display = step === totalSteps ? '' : 'none';
        currentStep = step;
    }

    function validateStep(step) {
        if (step === 1) {
            const consentError = document.getElementById('consentError');
            const consentChecked = document.getElementById('consentCheck').checked;
            consentError.style.display = consentChecked ? 'none' : '';
            return consentChecked;
        }
        if (step === 2) {
            const counselorId = document.getElementById('counselor_id').value;
            const date = document.getElementById('date').value;
            const slotChecked = document.querySelector('input[name="slot"]:checked');
            const contactName = document.getElementById('emergency_contact_name').value.trim();
            const contactPhone = document.getElementById('emergency_contact_phone').value.trim();
            return Boolean(counselorId && date && slotChecked && contactName && contactPhone);
        }
        return true;
    }

    nextBtn.addEventListener('click', () => {
        if (!validateStep(currentStep)) {
            if (currentStep === 2) {
                alert('Please select a counselor, date, slot, and fill in the required emergency contact details.');
            }
            return;
        }
        showStep(Math.min(currentStep + 1, totalSteps));
    });

    backBtn.addEventListener('click', () => {
        showStep(Math.max(currentStep - 1, 1));
    });

    wizardForm.addEventListener('submit', (e) => {
        if (!validateStep(1)) {
            e.preventDefault();
            showStep(1);
            return;
        }
        if (!validateStep(2)) {
            e.preventDefault();
            showStep(2);
            alert('Please select a counselor, date, slot, and fill in the required emergency contact details.');
        }
    });

    showStep(currentStep);

    const counselorSelect = document.getElementById('counselor_id');
    const dateInput = document.getElementById('date');
    const dateWeekendError = document.getElementById('dateWeekendError');
    const slotContainer = document.getElementById('slotContainer');

    function isWeekend(dateStr) {
        if (!dateStr) return false;
        const [y, m, d] = dateStr.split('-').map(Number);
        const day = new Date(y, m - 1, d).getDay();
        return day === 0 || day === 6;
    }

    async function loadSlots() {
        const counselorId = counselorSelect.value;
        const date = dateInput.value;
        if (!counselorId || !date) {
            slotContainer.innerHTML = '<p class="no-slots">Select a counselor and date to see available slots.</p>';
            return;
        }
        slotContainer.innerHTML = '<p class="no-slots">Loading slots...</p>';
        try {
            const res = await fetch(`{{ route('faculty.appointments.available-slots') }}?date=${date}&counselor_id=${counselorId}`);
            if (!res.ok || !res.headers.get('content-type')?.includes('application/json')) {
                slotContainer.innerHTML = '<p class="no-slots">Your session may have expired. Please refresh the page and log in again.</p>';
                return;
            }
            const slots = await res.json();
            if (!slots.length) {
                slotContainer.innerHTML = '<p class="no-slots">No slots available for this date.</p>';
                return;
            }
            slotContainer.innerHTML = slots.map((s, i) => `
                <label class="slot-option">
                    <input type="radio" name="slot" value="${s.label}" ${i === 0 ? 'required' : ''}>
                    ${s.label}
                </label>
            `).join('');
        } catch (e) {
            slotContainer.innerHTML = '<p class="no-slots">Could not load slots. Please refresh the page and try again.</p>';
        }
    }

    counselorSelect.addEventListener('change', loadSlots);
    dateInput.addEventListener('change', () => {
        if (isWeekend(dateInput.value)) {
            dateWeekendError.style.display = '';
            dateInput.value = '';
            loadSlots();
            return;
        }
        dateWeekendError.style.display = 'none';
        loadSlots();
    });

    document.querySelectorAll('.concern-checkbox').forEach((el) => {
        el.addEventListener('change', () => {
            const anySuicidal = document.querySelector('.concern-checkbox[data-suicidal="1"]').checked;
            document.getElementById('suicidalThoughtsInput').value = anySuicidal ? '1' : '0';
            document.getElementById('safetyMessage').classList.toggle('hidden', !anySuicidal);
        });
    });
@endif
</script>
@endsection
