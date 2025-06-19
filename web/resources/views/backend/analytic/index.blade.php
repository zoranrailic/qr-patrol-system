{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Analytic | ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Analytic</h1>
@stop

@section('content')
{{--Show message if any--}}
@include('layouts.flash-message')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Data</h3>
    </div>

    <div class="card-body">
        <!-- Filtering -->
        <div id="date_filter" class="form-inline">
            <Form action="" method="get" class="form-inline col-sm-12" autocomplete="off">
                <div class="form-group mb-2">
                    <label for="from"></label>
                    <div class="input-group">
                        <input type="text" name="from" class="form-control" id="from" placeholder="From Date" value="{{ $param['from'] }}">
                        <div class="input-group-append" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <label for="to"></label>
                    <div class="input-group">
                        <input type="text" name="to" class="form-control" id="to" placeholder="To Date" value="{{ $param['to'] }}">
                        <div class="input-group-append" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> Filter</button>
            </form>
        </div>
        <hr>
        <!-- Filtering -->

        <div class="row" id="reportPage">
            <!-- Chart -->
            <div class="col-sm-12" style="width:100%;">
                {!! $analytic->render() !!}
            </div>
            <!-- Chart -->
        </div>

        <div class="table-responsive datatables-margin-t45">
          <div class="alert alert-info" role="alert">
              Analytic only displays data for "workers who arrive late", "overtime work", and "early check-out".
          </div>
          <hr>
            {!! $html->table(['class' => 'table table-hover']) !!}
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker.css') }}">
<link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
@stop

@section('js')
<!--Data tables-->
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/jszip/jszip.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/pdfmake/pdfmake.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/pdfmake/vfs_fonts.js') }}"></script>
{{--Button--}}
<script src="{{ asset('vendor/datatables-plugins/buttons/js/dataTables.buttons.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.flash.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.colVis.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.html5.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.print.js') }}"></script>
{!! $html->scripts() !!}
<script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>
{{--Datepicker--}}
<script src="{{ asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    $(document).ready(function () {

        $('#from, #to').datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: 'TRUE',
            autoclose: true,
            changeMonth: true,
            changeYear: true
        });
    });
</script>
@stop
