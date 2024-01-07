@extends('frontend.amazy.layouts.app')

@section('title')
{{$content->mainTitle}}
@endsection
@section('content')
@php
    $page = \Modules\FrontendCMS\Entities\DynamicPage::where('slug', 'about-us')->first();
@endphp
<section class="padding-80px-tb about-section header-margin-top" style="padding-top: 80px !important;padding-bottom: 80px !important;background-size: cover;background-position: center;background-repeat: no-repeat;background-image: url({{url('/')}}/public/frontend/amazy/img/about-bg.png)">
<div class="container-cnt">
            <div class="row">
                <div class="col-md-4">
                    <img src="{{url('/')}}/public/frontend/amazy/img/about-girl.png" class="w-100" style="width:100%" alt="About Girl">
                </div>
                <div class="col-md-8">
                    <div class="content-area">
                        <h2>INTRODUCING</h2>
                        <h1>NATALIE SHARMA</h1>
                        <div class="description-box">
                        <p class="description">@php
                                                    echo $page->description;
                                                @endphp
                        </p>
                        <p class="description">
                            In 2019, Natalie decided to expand operations and felt nothing would be better than to develop and supply a new sporting brand in the UK. That's when FIGHTOR was born! FIGHTOR is owned and trademarked by Natalie's UK company- Sports Science Limited. The company has a vision to reach the world wide market and has it’s presence felt by first class athletes and as importantly, you the consumer.
                        </p>
                        <p class="description">
                            The brand is designed in the UK with state of the art technology, taking into consideration the finest raw materials which are environmentally sustainable. All types of sporting goods are manufactured in the FIGHTOR brand and now with an elite men's and women's clothing range, there is no better time to show the world that YOU are a FIGHTOR!!</p>
                        </div>
                    </div>
                </div>
            </div>
</div>
</section>
@endsection

