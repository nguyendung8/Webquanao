<?php

namespace App\Http\Controllers;

use App\Models\VpFavouriteProduct;
use App\Models\VpOrder;
use App\Models\VpProduct;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    public function getAddCart($id)
    {
        $product = VpProduct::find($id);
        Cart::add(['id' => $id, 'name' => $product->prod_name, 'qty' => 1,
                    'price' => $product->prod_price, 'weight' => 550, 'options' => ['img' => $product->prod_img]]);

        return redirect('cart/show');
    }
    public function getShowCart()
    {
        $total = Cart::total();
        $products = Cart::content();
        return view('frontend.cart', compact('products', 'total'));
    }
    public function getDeleteCart($id)
    {
        if($id == 'all') {
            Cart::destroy();
        } else {
            Cart::remove($id);
        }

        return back();
    }
    public function getUpdateCart(Request $request)
    {
        $rowId = $request->rowId;
        $quantity = $request->quantity;

        Cart::update($rowId, $quantity);
    }
    public function postPayCart(Request $request)
    {
        // add table order
        $order = new VpOrder;
        $order->name  = $request->name;
        $order->address = $request->add;
        $order->email = $request->email;
        $order->phone = $request->phone;
        $order->total_price = Cart::total();
        $order->total_products = Cart::content()->pluck('name')->implode('; ');
        $order->placed_order_date = now()->format('d/m/Y');
        $order->user_id = Auth::id();
        $order->save();

        $data['info'] = $request->all();
        $email = $request->email;
        $name = $request->name;
        $data['cart'] = Cart::content();
        $data['total'] = Cart::total();
        Cart::destroy();
        return redirect('complete');
    }
    public function getComplete()
    {
        return view('frontend.complete');
    }
    public function addFavourite(Request $request, $id)
    {
        $favourite_prod = new VpFavouriteProduct;
        $favourite_prod->user_id = Auth::id();
        $favourite_prod->favou_product = $id;

        $favourite_prod->save();
        return back()->with('success', 'Thêm sản phẩm vào danh sách yêu thích thành công!');
    }
}
