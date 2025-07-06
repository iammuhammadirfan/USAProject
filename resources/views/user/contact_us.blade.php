@extends('user.layout')
@section('content')
<div class="row justify-content-center">
        <div class="col-md-8">

            <!-- Page Header -->
            <div class="mb-4 text-center">
                <h1 class="mb-2">Contact Us</h1>
            </div>
            <div class="mb-4">
                <p class="text-muted">
                    We would love to hear from you. Fill out the form and someone will reply back within 24 hours.
    
                </p>
            </div>

            <!-- Card Box -->
            <div class="card shadow">
                <div class="card-body">

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Error Message -->
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Contact Form -->
                    <form action="{{ route('contact.send') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="attachment" class="form-label">Attachment (optional)</label>
                                <p class="text-muted mb-2" style="margin-top: -8px;">Screenshots for example</p>
                                <input type="file" name="attachment" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-warning">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
            <!-- End Card -->

        </div>
    </div>
@endsection
