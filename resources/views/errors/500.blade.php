@extends('user.layout')
@section('title','Error')
@section('content')
@section('error-styles')
    <style>
        .error-page
		{
			margin: 20% 10px;
			display: flex;
			align-items: center;
			flex-direction: column;
			gap: 10px;
		}
    </style>
@stop
    <section class="error-page">
		<span style="margin-bottom: 0.5em;
	font-size: 1.75em;
	color: #ea8a1a;">Oops!Something went Wrong.</span>
	<span><a href="/" 
		style="border-bottom: 1px dashed #837256;
		font-style: italic;
		text-decoration: none;
		color: #837256;">Please, go back this way.</a></span>
	</section>
@endsection






