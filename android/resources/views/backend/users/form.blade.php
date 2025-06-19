@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Users ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Users</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add or Update</h3>
        </div>

        {{ html()->form('POST', route($data->form_action))->attribute('autocomplete', 'off')->acceptsFiles()->open() }}
        {{ html()->hidden('id', $data->id)->id('user_id') }}

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Name</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->text('name', $data->name)->class('form-control')->required() }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User name.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Email</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->email('email', $data->email)->class('form-control')->required() }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User email, this email for login.
                    </small>
                </div>
            </div>

            <div id="form-password" class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Password</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->password('password')->id('password')->class('form-control')->attribute('autocomplete', 'new-password') }}
                    @if($data->page_type === 'edit')
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Leave it blank if you don't want to change
                        </small>
                    @else
                        <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> User password, this password for login.
                        </small>
                    @endif
                    <label class="reset-field-password" for="show-password"><input id="show-password" type="checkbox" name="show-password" value="1"> Show Password</label>
                </div>
            </div>

            {{--  image  --}}
            <div id="form-image" class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Image</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->file('image')->class('custom-file-input')->accept('image/gif, image/jpeg,image/jpg,image/png')->attribute('data-max-width', '800')->attribute('data-max-height', '400') }}
                    <label class="custom-file-label" for="customFile">Choose file</label>
                    <span class="image-upload-label"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the image (Recommended size: 160px × 160px, max 5MB)</span>
                    <div class="image-preview-area">
                        <div id="image_preview" class="image-preview">
                            @if ($data->page_type == 'edit')
                                <img src="{{ asset('uploads/'.$data->image) }}" width="160" title="image" class="img-circle elevation-2">
                            @else
                                <img src="{{ asset('img/default-user.png') }}" width="160" title="image" class="img-circle elevation-2">
                            @endif
                        </div>
                        {{-- only image has main image, add css class "show" --}}
                        <p class="delete-image-preview @if ($data->image != null && $data->image != 'default-user.png') show @endif" onclick="deleteImagePreview(this);"><i class="fa fa-window-close"></i></p>
                        {{-- delete flag for already uploaded image in the server --}}
                        {{ html()->hidden('image_delete') }}
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Role</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->select('role', $role, $data->role)->id('role')->class('form-control')->required() }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> User role.
                    </small>
                    @if($data->page_type == 'add')
                    <small class="form-text text-muted hide text-role">
                        <i class="fa fa-question-circle " aria-hidden="true"></i> We will also create the QR code. Access the QR from "History QR"
                    </small>
                    @endif
                </div>
            </div>

            @if($data->role == 2 || $data->role == 3)
                {{ html()->hidden('qr_id', $data->qr_id) }}
            @endif

        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    {{ html()->button($data->button_text)->type('submit')->name('submit')->id('btn-admin-member-submit')->class('btn btn-primary') }}
                </div>
            </div>
        </div>
        {{ html()->form()->close() }}
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
    <script src="{{ asset('js/backend/users/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
