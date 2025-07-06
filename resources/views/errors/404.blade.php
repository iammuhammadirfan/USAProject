@extends('user.layout')
@section('title','Page Not Found')
@section('content')
@section('error-styles')
    <style>
        
    </style>
@stop
    <section class="error-page" style="display: flex;
            align-items: center;
            gap: 50px;
            flex-direction: column;
            margin: 20%;">
		<span style="margin-bottom: 0.5em;
	font-size: 1.75em;
	color: #ea8a1a;">Sorry, Page Not Found!</span>
	<span><a href="/" 
		style="border-bottom: 1px dashed #837256;
		font-style: italic;
		text-decoration: none;
		color: #837256;">Please, go back this way.</a></span>
	</section>
@endsection






