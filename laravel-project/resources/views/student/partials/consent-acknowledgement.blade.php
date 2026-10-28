<h5>Student Information</h5>
<div class="form-group">
    <label>Name</label>
    <div class="consent-field-display">{{ $student->name }}</div>
</div>
<div class="form-group">
    <label>Department &amp; Semester</label>
    <div class="consent-field-display">{{ $student->department }} — {{ $student->semester }}</div>
</div>
<p>I, <strong>{{ $student->name }}</strong> (Name), from <strong>{{ $student->department }} — {{ $student->semester }}</strong> (Department &amp; Semester), am acknowledging that I have read, understood, and agree to participate in counseling under these conditions. I voluntarily release the Counselors providing services through counseling services at Beaconhouse National University and the University itself from liability for outcomes related to counseling sessions.</p>
