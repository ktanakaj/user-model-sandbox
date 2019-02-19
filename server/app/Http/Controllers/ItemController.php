<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PagingRequest;
use App\Models\Globals\UserItem;

/**
 * アイテムコントローラ。
 *
 * @OA\Tag(
 *   name="Items",
 *   description="アイテムAPI",
 * )
 */
class ItemController extends Controller
{
    /**
     * @OA\Get(
     *   path="/items",
     *   summary="アイテム一覧",
     *   description="認証中のユーザーのアイテム一覧を取得する。",
     *   tags={
     *     "Items",
     *   },
     *   security={
     *     {"SessionId":{}}
     *   },
     *   @OA\Parameter(
     *     in="query",
     *     name="page",
     *     description="ページ番号（先頭ページが1）",
     *     @OA\Schema(
     *       type="integer",
     *       default=1,
     *     ),
     *   ),
     *   @OA\Parameter(
     *     in="query",
     *     name="max",
     *     description="1ページ辺りの取得件数",
     *     @OA\Schema(
     *       type="integer",
     *       default=20,
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="アイテム一覧",
     *     @OA\JsonContent(
     *       type="object",
     *       allOf={
     *         @OA\Schema(ref="#components/schemas/Pagination"),
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(
     *             property="data",
     *             description="アイテム配列",
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserItem")
     *           ),
     *         ),
     *       }
     *     ),
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="未認証",
     *     @OA\JsonContent(ref="#components/schemas/Error"),
     *   ),
     * )
     */
    public function index(PagingRequest $request)
    {
        // ※ pageはpaginate内部で勝手に参照される模様
        return UserItem::where('user_id', Auth::id())->notEmpty()->paginate($request->input('max', 20));
    }
}
