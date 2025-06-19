@extends('adminlte::page')
<!-- page title -->
@section('title', 'Edit Profile | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Profile</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Update</h3>
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
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Your name.
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
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Your email, this email for login.
                    </small>
                </div>
            </div>

            <div id="form-password" class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Password</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->password('password')->id('password')->class('form-control')->attribute('autocomplete', 'new-password') }}
                      <small id="passwordHelpBlock" class="form-text text-muted">
                          Your password, this password for login. Leave it blank if you don't want to change
                      </small>
                    <label class="reset-field-password" for="show-password"><input id="show-password" type="checkbox" name="show-password" value="1"> Show Password</label>
                </div>
            </div>

            {{--  image  --}}
            <div id="form-image" class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Image</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ html()->file('image')->class('custom-file-input')->accept('image/gif, image/jpeg,image/jpg,image/png')->data('max-width', '800')->data('max-height', '400') }}
                    <label class="custom-file-label" for="customFile">Choose file</label>
                    <span class="image-upload-label"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the image (Recommended size: 160px × 160px, max 5MB)</span>
                    <div class="image-preview-area">
                        <div id="image_preview" class="image-preview">
                                <img src="{{ asset('uploads/'.$data->image) }}" width="160" title="image"
                                     class="img-circle elevation-2">
                        </div>
                        {{-- only image has main image, add css class "show" --}}
                        <p class="delete-image-preview @if ($data->image != null && $data->image != 'default-user.png') show @endif"
                           onclick="deleteImagePreview(this);"><i class="fa fa-window-close"></i></p>
                        {{-- delete flag for already uploaded image in the server --}}
                        {{ html()->hidden('image_delete') }}
                    </div>
                </div>
            </div>
        </div>

        @if($data->role == 2 || $data->role == 3)
            {{ html()->hidden('qr_id', $data->qr_id) }}
        @endif

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>
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
    <script src="{{ asset('js/backend/profile/form.js') }}"></script>
@stop
