<?php
	
	
namespace App\Http\Controllers;


use App\Models\Purchase;
use App\Models\Wager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WagerController extends Controller
{
	public function create(Request $request)
	{
		$post = json_decode($request->getContent(), true);
		$validator = Validator::make($post, [
			"total_wager_value" => "required|integer|min:1",
			"odds" => "required|integer|min:1",
			"selling_percentage" => "required|integer|between:1,100",
			"selling_price" => "required|regex:/^\d+(\.\d{1,2})?$/"
		]);
		
		if ($validator->fails()) {
			return response(['error' => $validator->getMessageBag()], 202);
		}
		if ($post['selling_price'] <= ($post['total_wager_value'] * ($post['selling_percentage']/100))) {
			return response([
				'error' => [
					'selling_price' => ['The selling price is invalid']
				]
			], 202);
		}
		$wager = new Wager();
		$wager->total_wager_value = $post['total_wager_value'];
		$wager->odds = $post['odds'];
		$wager->selling_percentage = $post['selling_percentage'];
		$wager->selling_price = $post['selling_price'];
		$wager->current_selling_price = $post['selling_price'];
		$wager->placed_at = now()->toDateTimeString();
		if (!$wager->save()) {
			return response([
				'error' => [
					'save_wager' => ['Something went wrong. Please try to create again']
				]
			], 202);
		}

		return response($wager, 201);
	}
	
	public function buy(Request $request, $id)
	{
		$post = json_decode($request->getContent(), true);
		$validator = Validator::make($post, [
			"buying_price" => "required|regex:/^\d+(\.\d{1,2})?$/"
		]);
		
		if ($validator->fails()) {
			return response(['error' => $validator->getMessageBag()], 202);
		}
		$buyingPrice = $post['buying_price'];
		$wager = Wager::find($id);
		if (empty($wager)) {
			return response([
				'error' => [
					'wager' => ['The wager is not found']
				]
			], 202);
		}
	
		if ($buyingPrice > $wager->current_selling_price) {
			return response([
				'error' => [
					'buy_price' => ['The buying price is invalid']
				]
			], 202);
		}
		
		$purchase = new Purchase();
		$purchase->wager_id = $id;
		$purchase->buying_price = $buyingPrice;
		$purchase->bought_at = now()->toDateTimeString();
		if (!$purchase->save()) {
			return response([
				'error' => [
					'save_purchase' => ['Something went wrong. Please try to create again']
				]
			], 202);
		}
		//Calculation is not given so I do it by my thought
		$wager->amount_sold = 1;
		$wager->percentage_sold = ($buyingPrice * 100)/$wager->current_selling_price;
		$wager->current_selling_price = $buyingPrice;
		$wager->update();
		
		return response($purchase, 201);
	}
	
	public function list(Request $request, $page, $limit)
	{
		$wagers = DB::table('wagers')->offset($page)->limit($limit)->get();
		
		return response($wagers, 200);
	}
}