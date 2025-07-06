@extends('user.layout')
@section('content')

@section('faq_styles')
    <style>
        .accordion-body {
                font-style: italic;
                padding-left: 1.5rem;
            }

            .accordion-button {
                font-weight: bold;
            }

            .faq-controls {
                text-align: right;
                margin-bottom: 1rem;
            }

            .img-fluid {
                max-width: 100%;
                height: auto;
            }
    </style>
@stop
<h1 class="mb-4 text-center">Frequently Asked Questions</h1>

<!-- Expand/Collapse All Buttons -->
<div class="faq-controls">
    <button class="btn btn-sm btn-outline-primary me-2" id="expandAllBtn">Expand All</button>
    <button class="btn btn-sm btn-outline-secondary" id="collapseAllBtn">Collapse All</button>
</div>

<!-- Dynamic Accordion -->
<div class="accordion" id="faqAccordion">
    @foreach ($faqs as $index => $faq)
        <div class="accordion-item">
            <h2 class="accordion-header" id="faqHeading{{ $index }}">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq{{ $index }}"
                        aria-expanded="false"
                        aria-controls="faq{{ $index }}">
                    {{ $index + 1 }}. {{ $faq->question }}
                </button>
            </h2>
            <div id="faq{{ $index }}" class="accordion-collapse collapse"
                aria-labelledby="faqHeading{{ $index }}"
                data-bs-parent="#faqAccordion"> 
                <div class="accordion-body">
                    {!! $faq->answer !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection



@section('faq_script')

 <script>
    
 <script>
@endsection
