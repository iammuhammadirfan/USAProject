@extends('admin.layout')
@section('content')
@section('view_user_styles')
	
	<!-- {{-- datatables --}} -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap.css"/>

    <style>


		.dt-search
		{
		  display:none;
		}

		.get-multiple-tickets
		{
			cursor:pointer;
		}
		/* Dropdown css code */
    .datatable-dropdown
    {
        display: flex;align-items: center;gap: 5px; width:70%; border-radius:5px;
    }
    .dropbtn {
		display: inline-block;
		color: white;
		text-align: center;
		padding: 14px 16px;
		text-decoration: none;
	}

        .dropdown:hover .dropbtn {
			background-color: red;
        }

        .dropdown {
			display: inline-block;
        }

        .dropdown-content {
           display: none;
           position: absolute;
           left: -100px;
           background-color: #f9f9f9;
           min-width: 160px;
           box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
           z-index: 1;
        }

        .dropdown-content a {
			color: black;
			padding: 12px 16px;
			text-decoration: none;
			display: block;
			text-align: left;
        }

        .dropdown-content a:hover {background-color: #f1f1f1;}

        .dropdown:hover .dropdown-content {
			display: block;
        }
		
		@media only screen and (max-width: 992px){
			
			
			.datatable-dropdown {
				width: 100%;
			}
	
        }
    </style>
@stop

<h2 class="text-center">All User Sign Ups</h2>
<table id="totalSignupsTable" class="table table-bordered">
	<thead class="bg-info text-white">
	<tr>
			<td>Date</td>
			<td>Number</td>
			<td>Type</td>
			<td>Checked Status</td>
			<td>Cancelled Status</td>
	</tr>
	</thead>
	<tbody>
	</tbody>
</table>

@endsection

@section('view_user_script')
	
	{{-- datatable --}}
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script> 
   <script type="text/javascript" src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
   <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.js"></script>
   
   <script>

        $(document).ready(function() {

			document.getElementById('backToBottom').style.visibility = "hidden";

			const url = window.location.href;

			const value = url.split('/view-user/')[1];

			$('#totalSignupsTable').DataTable({
				columnDefs: [{
                    "defaultContent": "-",
                    }],
                "iDisplayLength":25,
                processing:true,
                serverside:true,
                responsive:true,
                "ajax": {
                    "url": "/admin/view-user/"+value,
                    "type": "get",
                },
                columns: [
                    {  data: 'date',name:'date',orderable:true,searchable:true},
                    {  data: 'ticket_number',name:'ticket_number',orderable:true,searchable:true },
                    {  data: 'type',name:'type',orderable:true,searchable:true},
					{  data: 'checked_status',name:'checked_status',orderable:true,searchable:true},
					{  data: 'cancelled_status',name:'cancelled_status',orderable:true,searchable:true}
                ],
			});
		});
			
   </script>
@endsection