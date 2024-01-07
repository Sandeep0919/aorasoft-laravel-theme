@extends('frontend.amazy.layouts.app')
@section('styles')
    <style>
        .mb-15{
            margin-bottom: 15px!important;
        }
        .customer_img input{
            width: 100%;
            background: #fff;
        }
        .send_query .form-group input{
            text-transform: none!important;
        }
    </style>
@endsection
@section('title')
{{$contactContent->mainTitle}}
@endsection
@section('breadcrumb')
    {{ $contactContent->mainTitle }}
@endsection

@section('content')

                <section class="contact-section header-margin-top" style="background-image: url({{url('/')}}/public/frontend/amazy/img/contact-bg.png)">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-7 pb-none" style="margin-bottom: 20px;">
                                <div class="contact-title">
                                    <img src="{{url('/')}}/public/frontend/amazy/img/reachout.jpg" alt="">
                                </div>
                            </div>
                            <div class="col-md-5 pb-none">
                                <div class="contact-address">
                                    <div class="row  vt-grid">
                                        <div class="col-md-4 pb-none">
                                            <div class="ct-image-wrapper">
                                                <img src="{{url('/')}}/public/frontend/amazy/img/ninja.png" alt="">
                                            </div>
                                        </div>
                                        <div class="col-md-8 pb-none">
                                            <div class="icon-wrapper">
                                                <i class="fa fa-map-marker"></i>
                                                <div>
                                                    <h5>Metro Sports Industries</h5>
                                                    <address> 
                                                        
                                                        403,Leather Complex Kapurthala Road,
                                                        Jalandhar-144021(Punjab)India.
                                                    </address>
                                                </div>
                                            </div>
                                            <div class="icon-wrapper">
                                                <i class="fa fa-mobile"></i>
                                                    <a href="tel:+911815019758" class="ct-info">+91 1815019758</a>
                                            </div>
                                            <br />
                                            <?php
                                                $mail1 = "sales@fightorsports.com";
                                                $mail2 = "online@fightorsports.com";
                                                $mail3 = "kunal@fightorsports.com";
                                            ?>
                                            <div class="icon-wrapper">
                                                <i class="fa fa-envelope"></i>
                                                    <a href="mailto:{{ $mail1 }}" class="ct-info">{{ $mail1 }}</a>
                                            </div>
                                            <br />
                                            <div class="icon-wrapper">
                                                <i class="fa fa-envelope"></i>
                                                    <a href="mailto:{{ $mail2 }}" class="ct-info">{{ $mail2 }}</a>
                                            </div>
                                            <!--<br />-->
                                            <!--<div class="icon-wrapper">-->
                                            <!--    <i class="fa fa-envelope"></i>-->
                                            <!--        <a href="mailto:{{ $mail3 }}" class="ct-info">{{ $mail3 }}</a>-->
                                            <!--</div>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

@endsection
@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{config('app.map_api_key')?config('app.map_api_key'):'AIzaSyDfpGBFn5yRPvJrvAKoGIdj1O1aO9QisgQ'}}"></script>
<script src="{{url('/')}}/public/frontend/amazy/js/map.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>

    (function($){
        "use strict";

        $(document).ready(function() {

            $('#contactForm').on('submit', function(event) {
                event.preventDefault();
                @if(env('NOCAPTCHA_FOR_CONTACT') == "true" )
                    var response = grecaptcha.getResponse();
                    if(response.length == 0){
                        @if(env('NOCAPTCHA_INVISIBLE') != "true")
                        $('#error_g_recaptcha').text("The google recaptcha field is required");
                        return false;
                        @endif
                    }
                    @endif
                $("#contactBtn").prop('disabled', true);
                $('#contactBtn').text('{{ __('common.submitting') }}');

                var formElement = $(this).serializeArray()
                var formData = new FormData();
                formElement.forEach(element => {
                    formData.append(element.name, element.value);
                });
                if($('.custom_file').length > 0){
                    let photo = $('.custom_file')[0].files[0];
                    if (photo) {
                        formData.append($('.custom_file').attr('name'), photo)
                    }
                }
                formData.append('_token', "{{ csrf_token() }}");
                $.ajax({
                    url: "{{ route('contact.store') }}",
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(data) {
                        toastr.success("{{__('defaultTheme.message_sent_successfully')}}","{{__('common.success')}}");
                        $("#contactBtn").prop('disabled', false);
                        $('#contactBtn').text("{{ __('defaultTheme.send_message') }}");
                        resetErrorData();
                    },
                    error: function(data) {
                        toastr.error("{{__('common.error_message')}}", "{{__('common.error')}}");
                        $("#contactBtn").prop('disabled', false);
                        $('#contactBtn').text("{{ __('defaultTheme.send_message') }}");
                        showErrorData(data.responseJSON.errors)

                    }
                });
            });

            function showErrorData(errors){
                $('#contactForm #error_name').text(errors.name);
                $('#contactForm #error_email').text(errors.email);
                $('#contactForm #error_query_type').text(errors.query_type);
                $('#contactForm #error_message').text(errors.message);
            }

            function resetErrorData(){
                $('#contactForm')[0].reset();
                $('#contactForm #error_name').text('');
                $('#contactForm #error_email').text('');
                $('#contactForm #error_query_type').text('');
                $('#contactForm #error_message').text('');
            }

            if ($('#contact-map').length != 0) {
                var latitude = "{{ app('general_setting')->latitude }}";
                var longitude = "{{ app('general_setting')->longitude }}";
                google.maps.event.addDomListener(window, 'load', basicmap(parseFloat(latitude),parseFloat(longitude)));
            }

        });
    })(jQuery);


</script>
@endpush
