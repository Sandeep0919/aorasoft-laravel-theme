@php
    $headerSliderSection = $headers->where('type','slider')->first();
@endphp
<div class="home_banner bannerUi_active owl-carousel {{$headerSliderSection->is_enable == 0?'d-none':''}}">
    @php
        $sliders = $headerSliderSection->sliders();
    @endphp
    @if(count($sliders) > 0)
        @foreach($sliders as $key => $slider)
            <a class="banner_img" href="
                @if($slider->data_type == 'url')
                    {{$slider->url}}
                @elseif($slider->data_type == 'product')
                    {{singleProductURL(@$slider->product->seller->slug, @$slider->product->slug)}}
                @elseif($slider->data_type == 'category')
                    {{route('frontend.category-product',['slug' => @$slider->category->slug, 'item' =>'category'])}}
                @elseif($slider->data_type == 'brand')
                    {{route('frontend.category-product',['slug' => @$slider->brand->slug, 'item' =>'brand'])}}
                @elseif($slider->data_type == 'tag')
                    {{route('frontend.category-product',['slug' => @$slider->tag->name, 'item' =>'tag'])}}
                @else
                    {{url('/category')}}
                @endif
                " {{$slider->is_newtab == 1?'target="_blank"':''}}>
                <img class="img-fluid" src="{{showImage($slider->slider_image)}}" alt="{{@$slider->name}}" title="{{@$slider->name}}">
            </a>
        @endforeach
    @endif
</div>

<!-- ============================================================= SECTION – HERO ============================================================= -->
			
<section id="mainSlider">
    <div id="owl-main" class="owl-carousel">
        <div class="item" style="">
            <div class="slider-items">
                <div class="desktop-slide" style="background-image: url('https://fightorsports.com/public/frontend/amazy/img/banner-slider/1.png');"></div>
                <div class="mobile-slide" style="background-image: url('https://fightorsports.com/public/frontend/amazy/img/banner-slider/4.png');"></div>
            </div>
        </div>
        <!-- /.item -->

        <div class="item" style="">
            <div class="slider-items">
                <div class="desktop-slide" style="background-image: url('https://fightorsports.com/public/frontend/amazy/img/banner-slider/2.png');"></div>
                <div class="mobile-slide" style="background-image: url('https://fightorsports.com/public/frontend/amazy/img/banner-slider/5.png');"></div>
            </div>
        </div>
        <!-- /.item -->

        <div class="item" style="">
            <div class="slider-items">
                <div class="desktop-slide" style="background-image: url('https://fightorsports.com/public/frontend/amazy/img/banner-slider/3.png');"></div>
                <div class="mobile-slide" style="background-image: url('https://fightorsports.com/public/frontend/amazy/img/banner-slider/6.png');"></div>
            </div>
        </div>
        <!-- /.item -->
    </div>
    <!-- /.owl-carousel -->
</section>
<style>
    #mainSlider .slider-items {
        width: 100%;
        height: 734px;
    }
    @media (max-width: 1600px){
        #mainSlider .slider-items {
            width: 100%;
            height: 587px;
        }
    }
    section#content-desktop.hero-style2 {
        display: none;
    }
    @media (max-width: 1200px){
        #mainSlider .slider-items {
            width: 100%;
            height: 471px;
        }
    }
    #mainSlider .slider-items .desktop-slide,
    #mainSlider .slider-items .mobile-slide {
        width: 100%;
        -o-object-fit: cover;
        object-fit: cover;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: norepeat;
    }

    #mainSlider .slider-items .desktop-slide {
        width: 100%;
        -o-object-fit: cover;
        object-fit: cover;
        height: 100%;
    }

    #mainSlider .slider-items .mobile-slide {
        display: none;
    }

    @media (max-width: 767px) {
        #mainSlider .slider-items .desktop-slide {
            display: none;
        }
    #mainSlider .slider-items {
    width: 100%;
    height: 893px;
}
        #mainSlider .slider-items .mobile-slide {
            display: block;
        }
        section#content-mobile{
            display: none !important;
        }
    }
    
       @media (max-width: 425px) {
    #mainSlider .slider-items {
    width: 100%;
    height: 893px;
}
}
   @media (max-width: 345px) {
    #mainSlider .slider-items {
    width: 100%;
    height: 800px;
}
}
     @media (max-width: 320px) {
    #mainSlider .slider-items {
    width: 100%;
    height: 695px;
}
}
</style>

			<script>
			    

			</script>
			