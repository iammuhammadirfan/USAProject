@extends('user.layout')
@section('title','Session Expired')
@section('content')
@section('error-styles')
    <style>
        /* .error-page
		{
			margin: 40% 10px;
			display: flex;
			align-items: center;
			flex-direction: column;
			gap: 10px;
		} */
    </style>
@stop
     <section class="error-page" style="display: flex;
            align-items: center;
            gap: 50px;
            flex-direction: column;
            margin: 20%;">
		<span style="margin-bottom: 0.5em;
	font-size: 1.75em;
	color: #ea8a1a;">Session has Expired</span>
	<span><a href="/" 
		style="border-bottom: 1px dashed #837256;
		font-style: italic;
		text-decoration: none;
		color: #837256;">Please, go back this way.</a></span>
	</section>
@endsection






