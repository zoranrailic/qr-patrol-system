@extends('adminlte::page')
<!-- page title -->
@section('title', 'Settings | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Settings</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Update</h3>
        </div>

        {!! html()->form('POST', route($data->form_action))->attribute('autocomplete', 'off')->attribute('files', true)->open() !!}
        {!! html()->hidden('id', $data->id)->id('user_id') !!}

        <div class="card-body">
            <div class="row">
                <div class="col-md-7">
                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Start Time</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {!! html()->text('start_time', $data->start_time)->class('form-control')->required()->id('start_time') !!}
                            <p class="form-text text-muted">Fill with the start time to enter the office</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Out Time</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {!! html()->text('out_time', $data->out_time)->class('form-control')->required()->id('out_time') !!}
                            <p class="form-text text-muted">Fill with office hours</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Url</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {!! html()->text('url', url('/'))->class('form-control')->disabled()->id('url') !!}
                            <p class="form-text text-muted">Your current URL. You cannot change this URL</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Key App</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {!! html()->text('key_app', $data->key_app)->class('form-control')->required()->id('key')->attribute('readonly', 'readonly') !!}
                            <p class="form-text text-muted">Application Key is used for communication with the Application. You can change the key by clicking on the button "Generate New Key" don't forget to save it</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Timezone</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {!! html()->select('timezone', $timezone, $data->timezone)->id('timezone')->class('form-control select2') !!}
                            <p class="form-text text-muted">Fill in the Timezone you are</p>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <span class="img-responsive img-thumbnail" style="margin: 0 auto;display: block;">{!! QrCode::size(150)->generate($data->qr) !!}</span>
                    <p class="text-center"><b>QR Code</b></p>
                    <p class="text-center form-text text-muted">This QR code is used for the first time opening the App. <br>Scan this QR and this is done only once.</p>
                    <p class="text-center">
                        <a href="{{ route('settings.downloadSettingsQrCode') }}" target="_blank">
                            <button type="button" class="btn btn-success">Download</button>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    {!! html()->submit($data->button_text)->name('submit')->id('btn-admin-member-submit')->class('btn btn-primary') !!}

                    <button type="button" id="generate-key" class="btn btn-primary">Generate New Key</button>
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
    <script src="{{ asset('js/backend/settings/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
