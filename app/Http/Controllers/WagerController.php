<?php
	
	
namespace App\Http\Controllers;


use App\Models\Purchase;
use App\Models\Wager;
use Illuminate\Http\Request;
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
		$wager = Wager::find($id);
		if (empty($wager)) {
			return response([
				'error' => [
					'wager' => ['The wager is not found']
				]
			], 202);
		}
	
		if ($post['buying_price'] > $wager->current_selling_price) {
			return response([
				'error' => [
					'buy_price' => ['The buying price is invalid']
				]
			], 202);
		}
		
		$purchase = new Purchase();
		$purchase->wager_id = $id;
		$purchase->buying_price = $post['buying_price'];
		$purchase->bought_at = now()->toDateTimeString();
		if (!$purchase->save()) {
			return response([
				'error' => [
					'save_purchase' => ['Something went wrong. Please try to create again']
				]
			], 202);
		}
	}
}