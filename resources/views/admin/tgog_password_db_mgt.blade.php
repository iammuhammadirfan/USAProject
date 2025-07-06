@extends('admin.layout')
@section('content')
@section('admin_tool_styles')

<!-- sweetalert -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

{{-- alertify --}}
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
<!-- Default theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css"/>
<!-- Semantic UI theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/semantic.min.css"/>

<style>

    .update-password-form
    {
        display: flex;
        gap: 20px;
        align-items: center;
        padding: 20px;
    }

    .case-number-input
    {
        width:60%;
    }

    
</style>
@stop

@if (Session::has('success'))
    <div class="alert alert-success">
        {{ Session::get('success') }}
    </div>
@endif
<?php
    // Fetch user with role '2'
    $user = DB::table('users')->where('role', '2')->first();
    
    // Check if user exists and is_enabled field is set
    if ($user && $user->is_enabled == 1) {
        $status = 'Enabled';
    } else {
        $status = 'Disabled';
    }
    
    $admin = DB::table('users')->where('role', '1')->first();
?>

<!-- <div class="card p-3 shadow"> -->
    <h3 style="text-align: center;">Manage Database</h3>
    <div style="display: flex;align-items: center;gap: 10px;">
        <div style="width: 47%;">
            <h3 style="text-align: center;">Load New Database File</h3>
            <div class="update-password-form">
                <form action="{{ route('admin.import.excel') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input class="form-control" type="file" name="file" id="formFile">
                    <button type="submit"
                class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2">Save</button>
                </form>
            </div>

            <a class="btn btn-success mx-auto" href="{{ route('admin.export.excel') }}">Export Users Data</a>
        </div>
    </div>
<!-- </div> -->
@endsection

@section('admin_tool_script')
{{-- alertify --}}
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

<script>

</script>
@endsection