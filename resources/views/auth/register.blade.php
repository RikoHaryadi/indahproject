@extends('layout.mainlayout')

@section('title','Create Account')

@section('content')
<div class="container mt-5">
  <h2>Create Account</h2>
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <form method="POST" action="{{ route('register.post') }}">
    @csrf

    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" 
             class="form-control" 
             id="username" 
             name="username" 
             value="{{ old('username') }}" 
             required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" 
             class="form-control" 
             id="password" 
             name="password" 
             required>
    </div>

    <div class="mb-3">
      <label for="password_confirmation" class="form-label">Confirm Password</label>
      <input type="password" 
             class="form-control" 
             id="password_confirmation" 
             name="password_confirmation" 
             required>
    </div>

    <div class="mb-3">
      <label for="level" class="form-label">Level User</label>
      <select class="form-select" id="level" name="level" required>
        <option value="" disabled {{ old('level')? '' : 'selected' }}>-- pilih level --</option>
        <option value="1" @if(old('level')==1) selected @endif>Sales</option>
        <option value="2" @if(old('level')==2) selected @endif>SPV Admin</option>
        <option value="3" @if(old('level')==3) selected @endif>Admin</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Create Account</button>
    <a href="{{ route('login') }}" class="btn btn-link">Back to Login</a>
  </form>
</div>
@endsection
