<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>GoGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        
        .box {
    border: 2px solid black;
    border-radius: 10px; /* Optional: adds rounded corners */
    padding: 20px;
    /* Optional: adds space inside the box */
}
 

    </style>
 
 <style>
       

        .custom-div {
            background-color: black;
            color: red;
        }
        
           .serving-number {
       color:red; font-size: 32em;
    }
  @media(max-width:768px)
  {
         .serving-number {
         font-size: 20em !important;
    }
  }
    </style>
    </head>
<body class="bg-white">
    <div class="container-fluid px-2 px-md-5 mt-5">
      
           
            
                <div class="row mt-2">
                    
                    <div class=" col-lg-2 col-md-4 col-sm-6 ">
                       <a  class="btn btn-warning  w-100 px-5 border border-dark py-1 mb-2" style="border: 1px solid #343a40; text-decoration: none;" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    Log Off
</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                       
                    </div>
                    
                    
                </div>
                 <div class="row mt-4">
            <div class="col-lg-12 col-md-12 ">
                
        <p class="h3">
  {{ \Carbon\Carbon::parse(Auth::user()->last_token_date)->format('F d, Y') }}
  {{ \Carbon\Carbon::parse(Auth::user()->token_interval_time)->format('h:i:s A') }}


</p>




            </div>
        </div>
           
     



<div class="row mt-2">
    <div class="col-12  ">
    <section class="mt-5 "><p   class="h3 text-start">Your Number</p></section>
    </div>
    </div>
    
    
    <div class="row bg-dark p-2 mt-3" >
        <div class="col-md-4"></div>
        <div class="col-md-4 d-flex justify-content-center">
            <h1 class="serving-number">{{ Auth::user()->token }}</h1>
        </div>
        <div class="col-md-4"></div>
    </div>
    
        

       

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <section class="text-center mt-3">
                    <p class="h3">Projected Return Time:
                        <b>
                            {{ App\Helpers\projectTime::get_time(Auth::user()->id) ?? ''}}
                        </b>
                    </p>
                </section>
            </div>
        </div>

        <div class="row">
            <div class=" col-sm-6 d-flex justify-content-center justify-content-sm-start">
                <p class="h4">Name:</p>&nbsp;&nbsp;
                <p class="h4 fw-bold">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</p>
            </div>
            
            <div class=" col-sm-6 d-flex   justify-content-center justify-content-sm-end">
                <p class="h4">Case Number:</p>&nbsp;&nbsp;
                <p class="h4 fw-bold">{{ Auth::user()->case_number }}</p>
            </div>
        </div>

        <div class="row my-5">
            <div class="col-lg-6 ">
                <a href="{{ route('user.print') }}" class="btn btn-warning btn-lg px-5 border border-dark py-1 mb-2 " style="border: 1px solid #343a40; text-decoration: none;">Print</a>

            </div>
            
            <div class="col-lg-6 col-md-6">
             <a href="{{ url('/') }}" class=" btn btn-warning btn-lg px-5 border border-dark py-1 mb-2" style="border: 1px solid #343a40; text-decoration: none;">View Current Number Being Served</a>

            </div>
        </div>
    </div>
    
    
    
    
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>


</html>