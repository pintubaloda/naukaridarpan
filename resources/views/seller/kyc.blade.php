@extends('layouts.app')
@section('title','KYC Verification — Naukaridarpan Seller')
@section('content')
<div class="container section">
  <div class="dash-layout">
    @include('components.seller-sidebar')
    <main>
      <h2 class="mb-1">KYC Verification</h2>
      <p class="text-muted mb-4">Required to request payouts. Verify your identity and bank account.</p>
      @if(session('success'))<div class="alert alert-success mb-3">{{ session('success') }}</div>@endif
      @if($errors->any())<div class="alert alert-error mb-3">{{ $errors->first() }}</div>@endif

      @if($kyc)
        <div class="alert {{ $kyc->status==='approved'?'alert-success':($kyc->status==='rejected'?'alert-error':'alert-warning') }} mb-4">
          <strong>KYC Status: {{ ucfirst(str_replace('_',' ',$kyc->status)) }}</strong>
          @if($kyc->status==='approved') — You can now request payouts.@endif
          @if($kyc->status==='under_review') — Our team is reviewing your documents. Usually takes 1-2 business days.@endif
          @if($kyc->status==='rejected') — {{ $kyc->rejection_reason }}. Please resubmit.@endif
        </div>
      @endif

      @if(!$kyc || $kyc->status === 'rejected')
      <div class="card card-static" style="max-width:640px">
        <div class="card-body">
          <form action="{{ route('seller.kyc.submit') }}" method="POST" enctype="multipart/form-data">@csrf
            <div style="margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border-l)">
              <h3 style="font-size:1rem;margin-bottom:1rem">Identity Documents</h3>
              <div class="form-group"><label class="form-label">PAN Number *</label><input type="text" name="pan_number" class="form-control" style="text-transform:uppercase" placeholder="ABCDE1234F" maxlength="10" required value="{{ old('pan_number',$kyc->pan_number??'') }}"></div>
              <div class="form-group"><label class="form-label">PAN Card Document * <span class="text-muted" style="font-size:.78rem">(PDF/JPG/PNG, max 5MB)</span></label><input type="file" name="pan_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
              <div class="form-group"><label class="form-label">Aadhaar Number *</label><input type="text" name="aadhaar_number" class="form-control" placeholder="1234 5678 9012" maxlength="12" required value="{{ old('aadhaar_number',$kyc->aadhaar_number??'') }}"></div>
              <div class="form-group"><label class="form-label">Aadhaar Document * <span class="text-muted" style="font-size:.78rem">(front and back, PDF/JPG/PNG)</span></label><input type="file" name="aadhaar_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
            </div>
            <div>
              <h3 style="font-size:1rem;margin-bottom:1rem">Bank Account Details</h3>
              <div class="form-group"><label class="form-label">Bank Name *</label><input type="text" name="bank_name" class="form-control" placeholder="State Bank of India" required value="{{ old('bank_name',$kyc->bank_name??'') }}"></div>
              <div class="g-grid" style="grid-template-columns:1fr 1fr;gap:.75rem">
                <div class="form-group" style="margin:0"><label class="form-label">Account Number *</label><input type="text" name="account_number" class="form-control" placeholder="1234567890" required value="{{ old('account_number',$kyc->account_number??'') }}"></div>
                <div class="form-group" style="margin:0"><label class="form-label">IFSC Code *</label><input type="text" name="ifsc_code" class="form-control" style="text-transform:uppercase" placeholder="SBIN0001234" maxlength="11" required value="{{ old('ifsc_code',$kyc->ifsc_code??'') }}"></div>
              </div>
              <div class="form-group mt-2"><label class="form-label">Bank Proof * <span class="text-muted" style="font-size:.78rem">(Cancelled cheque / Passbook / Bank statement)</span></label><input type="file" name="bank_proof_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required></div>
            </div>
            <div class="alert alert-info mt-2 mb-3" style="font-size:.85rem">All documents are encrypted and stored securely. Used only for identity verification and payout processing.</div>
            <button type="submit" class="btn btn-primary btn-lg">Submit for Verification</button>
          </form>
        </div>
      </div>
      @else
      <div class="card card-static card-body" style="max-width:500px">
        <h3 style="font-size:1rem;margin-bottom:1rem">Submitted Documents</h3>
        @foreach([['PAN Number',$kyc->pan_number],['Aadhaar Number',substr($kyc->aadhaar_number,0,4).'XXXXXXXX'],['Bank Name',$kyc->bank_name],['Account Number','XXXXXX'.substr($kyc->account_number,-4)],['IFSC Code',$kyc->ifsc_code]] as [$l,$v])
        <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border-l);font-size:.88rem"><span class="text-muted">{{ $l }}</span><span class="fw-600">{{ $v }}</span></div>
        @endforeach
      </div>
      @endif
    </main>
  </div>
</div>
@endsection
