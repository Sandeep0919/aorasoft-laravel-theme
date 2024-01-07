<?php

namespace Modules\GiftCard\Repositories;

use App\Models\Cart;
use App\Traits\ImageStore;
use Carbon\Carbon;
use App\Models\OrderProductDetail;
use Maatwebsite\Excel\Facades\Excel;
use Modules\GiftCard\Imports\GiftCardImport;
use Modules\GiftCard\Entities\GiftCard;
use Modules\GiftCard\Entities\GiftCardUse;
use Modules\GiftCard\Entities\GiftCardGalaryImage;
use Modules\Shipping\Entities\ShippingMethod;
use Modules\OrderManage\Repositories\OrderManageRepository;
use App\Traits\SendMail;
use Modules\GiftCard\Entities\AddGiftCard;
use Modules\GiftCard\Entities\DigitalGiftCard;
use Modules\GiftCard\Entities\GiftCardTag;
use Modules\GiftCard\Entities\GiftCoupon;
use Modules\GiftCard\Imports\GiftProductImport;
use Modules\Setup\Entities\Tag;

class GiftCardRepository
{
    use ImageStore, SendMail;

    
    public function getAll(){
        return GiftCard::latest();
    }
    public function store($data)
    {
        if ($data['product_type'] == 1) {
            if (!empty($data['thumbnail_image'])) {
                $data['thumbnail_image'] = ImageStore::saveImage($data['thumbnail_image'], 165, 165);
            }
            $card = GiftCard::create([
                'name' => $data['name'],
                'sku' => $data['sku'],
                'selling_price' => $data['selling_price'],
                'discount' => $data['discount'],
                'discount_type' => $data['discount_type'],
                'start_date' => ($data['start_date']) ? Carbon::parse($data['start_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                'end_date' => ($data['end_date']) ? Carbon::parse($data['end_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                'thumbnail_image' => $data['thumbnail_image'] ? $data['thumbnail_image'] : null,
                'status' => $data['status'],
                'description' => $data['description'],
                'shipping_id' => 1
            ]);
            $tags = [];
            $tags = explode(',', $data['tags']);
            foreach ($tags as $key => $tag) {
                $tag = Tag::where('name', $tag)->updateOrCreate([
                    'name' => $tag
                ]);
                GiftCardTag::create([
                    'gift_card_id' => $card->id,
                    'tag_id' => $tag->id,
                ]);
            }
            if (!empty($data['galary_image'])) {
                foreach ($data['galary_image'] as $key => $image) {
                    $image_name = ImageStore::saveImage($image, 1000, 1000);
                    GiftCardGalaryImage::create([
                        'image_name' => $image_name,
                        'gift_card_id' => $card->id
                    ]);
                }
            }
        }else {
            if (!empty($data['thumbnail_image_one'])) {
                $thumbnail_image_one = ImageStore::saveImage($data['thumbnail_image_one'], 165, 165);
            }
            if (!empty($data['thumbnail_image_two'])) {
                $thumbnail_image_two = ImageStore::saveImage($data['thumbnail_image_two'], 400, 400);
            }
            $card = DigitalGiftCard::create([
                'gift_name' => $data['gift_name'],
                'descriptionOne' => $data['descriptionOne'],
                'thumbnail_image_one' => $thumbnail_image_one ? $thumbnail_image_one : null,
                'thumbnail_image_two' => $thumbnail_image_two ? $thumbnail_image_two : null,
            ]);
            foreach ($data['section'] as $addGifgtCard){
                $date = explode('to', $addGifgtCard['gift_expire_date']);
               $sections = AddGiftCard::create([
                    'digilat_gift_id' => $card->id,
                    'gift_card_value' => (integer)gv($addGifgtCard, 'gift_card_value',null),
                    'gift_selling_price' => (integer)gv($addGifgtCard, 'gift_selling_price',null),
                    'gift_discount_type' => gv($addGifgtCard, 'gift_discount_type',null),
                    'gift_discount_amount' => (integer)gv($addGifgtCard, 'gift_discount_amount',null),
                    'start_date' =>  Carbon::parse($date[0])->format('Y-m-d'),
                    'end_date' =>  Carbon::parse($date[1])->format('Y-m-d'),
                    'number_of_gift_card' => (integer)gv($addGifgtCard, 'number_of_gift_card',null),
                ]);
                if(gv($addGifgtCard, 'upload_img_file')){
                    Excel::import(new GiftProductImport($sections->id), gv($addGifgtCard, 'upload_img_file')->store('temp'));
                }else{
                    foreach($addGifgtCard['gift_selling_coupon'] as $giftCoupon){
                        GiftCoupon::create([                       
                            'gift_selling_coupon' => $giftCoupon,
                            'add_gift_id'=>$sections->id,                     
                        ]);
                    }
                }
            }
        }
        return true;
    }
    public function statusChange($data){
        return GiftCard::where('id', $data['id'])->update([
            'status' => $data['status']
        ]);
    }
    public function getById($id){
        return GiftCard::findOrFail($id);
    }
   
    public function update($data, $id)
    {
        $card = GiftCard::findOrFail($id);
        if(!empty($data['thumbnail_image'])){
            ImageStore::deleteImage($card->thumbnail_image);
            $data['thumbnail_image'] = ImageStore::saveImage($data['thumbnail_image'], 300, 300);
        }
        $card->update([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'selling_price' => $data['selling_price'],
            'discount' => $data['discount'],
            'discount_type' => $data['discount_type'],
            'start_date' => isset($data['start_date']) ? Carbon::parse($data['start_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'end_date' => isset($data['end_date']) ? Carbon::parse($data['end_date'])->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
            'thumbnail_image' => isset($data['thumbnail_image'])?$data['thumbnail_image']:$card->thumbnail_image,
            'status' => $data['status'],
            'description' => $data['description'],
            'shipping_id' => 1
        ]);

        //for tag start
        $tags = [];
        $tags = explode(',', $data['tags']);
        $oldtags = GiftCardTag::where('gift_card_id', $id)->whereHas('tag', function($q)use($tags){
            $q->whereNotIn('name',$tags);
        })->pluck('id');
        GiftCardTag::destroy($oldtags);

        foreach ($tags as $key => $tag) {
            $tag = Tag::where('name', $tag)->updateOrCreate([
                'name' => $tag
            ]);
            GiftCardTag::where('gift_card_id', $card->id)->where('tag_id', $tag->id)->updateOrCreate([
                'gift_card_id' => $card->id,
                'tag_id' => $tag->id,
            ]);
        }
        // for tag end
        if(!empty($data['galary_image'])){

            $images = GiftCardGalaryImage::where('gift_card_id', $id)->get();
            foreach($images as $img){
                ImageStore::deleteImage($img->image_name);
                $img->delete();
            }
            foreach($data['galary_image'] as $key => $image){
                $image_name = ImageStore::saveImage($image,1000,1000);
                GiftCardGalaryImage::create([
                    'image_name' => $image_name,
                    'gift_card_id' => $card->id
                ]);
            }
        }
        return true;
    }
    public function getGiftCardById($id){
        return DigitalGiftCard::findOrFail($id);
    }
     public function digitalCardUpdate($data, $id){
        $card = DigitalGiftCard::findOrFail($id);
        if(!empty($data['thumbnail_image_one'])){
            ImageStore::deleteImage($card->thumbnail_image_one);
            $thumbnail_image_one = ImageStore::saveImage($data['thumbnail_image_one'], 165, 165);
        }else {
            $thumbnail_image_one = $card->thumbnail_image_one;
        }
        if(!empty($data['thumbnail_image_two'])){
            ImageStore::deleteImage($card->thumbnail_image_two);
            $thumbnail_image_two = ImageStore::saveImage($data['thumbnail_image_two'], 400, 400);
        }else {
            $thumbnail_image_two = $card->thumbnail_image_two;
        }
        $card->update([
            'gift_name' => $data['gift_name'],
            'descriptionOne' => $data['descriptionOne'],
            'thumbnail_image_one' => $thumbnail_image_one ? $thumbnail_image_one : null,
            'thumbnail_image_two' => $thumbnail_image_two ? $thumbnail_image_two : null,
        ]);
        foreach ($card->addGiftCard as $addGiftCard) {
            $giftcard = AddGiftCard::findOrFail($addGiftCard->id);
            foreach ($giftcard->giftCoupons as $giftcoupon) {
                $couponcode = GiftCoupon::findOrFail($giftcoupon->id);
                if ($couponcode) {
                    $couponcode->delete();
                }
            }
            if ($giftcard) {
                $giftcard->delete();
            }
        }
        foreach ($data['section'] as $addGifgtCard){
            $date = explode('to', $addGifgtCard['gift_expire_date']);
            $sections = AddGiftCard::create([
                 'digilat_gift_id' => $card->id,
                 'gift_card_value' => (integer)gv($addGifgtCard, 'gift_card_value',null),
                 'gift_selling_price' => (integer)gv($addGifgtCard, 'gift_selling_price',null),
                 'gift_discount_type' => gv($addGifgtCard, 'gift_discount_type',null),
                 'gift_discount_amount' => (integer)gv($addGifgtCard, 'gift_discount_amount',null),
                 'start_date' =>  Carbon::parse($date[0])->format('Y-m-d'),
                 'end_date' =>  Carbon::parse($date[1])->format('Y-m-d'),
                 'number_of_gift_card' => (integer)gv($addGifgtCard, 'number_of_gift_card',null),
             ]);
 
             if(gv($addGifgtCard, 'upload_img_file')){
                 Excel::import(new GiftProductImport($sections->id), gv($addGifgtCard, 'upload_img_file')->store('temp'));
             }else{
                 foreach($addGifgtCard['gift_selling_coupon'] as $giftCoupon){
                     GiftCoupon::create([                       
                         'gift_selling_coupon' => $giftCoupon,
                         'add_gift_id'=>$sections->id,                     
                     ]);
                 }
             }
         }
         return true;
     }
    public function deleteById($id){
        $card = GiftCard::findOrFail($id);
        $listInCart = Cart::where('product_type','gift_card')->where('product_id', $card->id)->pluck('id')->toArray();
        Cart::destroy($listInCart);
        $existProduct = OrderProductDetail::where('type', 'gift_card')->where('product_sku_id', $card->id)->first();
        if ($existProduct) {
            return "not_possible";
        }
        foreach($card->galaryImages as $image){
            ImageStore::deleteImage($image->image_name);
            $image->delete();
        }
        ImageStore::deleteImage($card->thumbnail_image);
        $card->delete();
        return 'possible';
    }
    public function giftDeleteById($id){ 
        $giftCardData = DigitalGiftCard::findOrFail($id);
        ImageStore::deleteImage($giftCardData->thumbnail_image_one);
        ImageStore::deleteImage($giftCardData->thumbnail_image_two);
        $giftCardData->delete();
        return 'possible';
    }


    public function getShipping(){
        return ShippingMethod::where('is_active', 1)->get();
    }
    public function send_code_to_mail($data)
    {
        $orderRepo = new OrderManageRepository;
        $order = $orderRepo->findOrderByID($data['order_id']);
        $gift_card = $this->getById($data['gift_card_id']);
        $secret_code = date('ymd-his').'-'.rand(111,999).$order->id.'-'.$gift_card->id.rand(1111,9999);
        try {
            $this->sendGiftCardSecretCodeMail($order, $data['mail'], $gift_card, $secret_code);
            $this->storeGiftCardData($secret_code, $order->id, $gift_card->id, 1, $data['qty']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function storeGiftCardData($secret_code, $order_id, $gift_card_id, $is_mail_sent, $qty)
    {
        $existGiftCardInfo = GiftCardUse::where('gift_card_id',$gift_card_id)->where('order_id',$order_id)->first();
        if ($existGiftCardInfo == null) {
            GiftCardUse::create([
                'gift_card_id' => $gift_card_id,
                'order_id' => $order_id,
                'qty' => $qty,
                'secret_code' => $secret_code,
                'is_mail_sent' => $is_mail_sent,
                'mail_sent_date' => Carbon::now()->format('Y-m-d')
            ]);
        }else {
            $existGiftCardInfo->update([
                'secret_code' => $secret_code,
                'mail_sent_date' => Carbon::now()->format('Y-m-d')
            ]);
        }
    }
    public function csvUploadCategory($data)
    {
        Excel::import(new GiftCardImport, $data['file']->store('temp'));
    }
    public function giftCardUseStatus($data){
        $existGiftCardInfo = GiftCardUse::where('gift_card_id',$data['gift_card_id'])->where('order_id',$data['order_id'])->first();
        if($existGiftCardInfo){
            if(!$existGiftCardInfo->is_used){
                return true;
            }
            return false;
        }
        return true;
    }
}
