@extends('adminlte::page')
<!-- page title -->
@section('title', 'Import Data Qr ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Import Data Qr</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import</h3>
        </div>

        {!! html()->form('POST', route($data->form_action))->attribute('autocomplete', 'off')->acceptsFiles()->open() !!}
        {!! html()->hidden('id', $data->id)->id('id') !!}

        <div class="card-body">

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Import Data</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {!! html()->file('import')->class('custom-file-input')->id('import')->required() !!}
                    {!! html()->label('Choose file')->class('custom-file-label')->for('customFile') !!}
                    <span class="image-upload-label"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the csv File</span>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Download Template</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <a href="{{ asset('img/template.csv') }}" download="">
                      {!! html()->button('Download Template CSV')->type('button')->class('btn btn-success') !!}
                    </a>
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Download CSV file template.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Instructions</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {!! html()->button('Show Instructions')->type('button')->class('btn btn-info collapsed')->attribute('data-toggle', 'collapse')->attribute('data-target', '#instructions')->attribute('aria-expanded', 'false') !!}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> How to fill data to CSV file.
                    </small>
                    <div id="instructions" class="collapse col-content" aria-expanded="false">
                        {!! html()->img()->src(asset('img/import_csv.png'))->class('img-responsive') !!}
                    </div>
                </div>
            </div>

        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    {!! html()->button($data->button_text)->type('submit')->name('submit')->id('btn-admin-member-submit')->class('btn btn-primary') !!}
                </div>
            </div>
        </div>
        {!! html()->form()->close() !!}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')

@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>

    <script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
