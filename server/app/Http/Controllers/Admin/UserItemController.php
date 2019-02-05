<?php

namespace App\Http\Controllers\Admin;

use App\Models\Globals\UserItem;
use App\Http\Controllers\Controller;

/**
 * ユーザーアイテムコントローラ。
 *
 * @OA\Schema(
 *   schema="UserItem",
 *   type="object",
 *   @OA\Property(
 *     property="id",
 *     description="ユーザーアイテムID",
 *     type="number",
 *   ),
 *   @OA\Property(
 *     property="user_id",
 *     description="ユーザーID",
 *     type="number",
 *   ),
 *   @OA\Property(
 *     property="item_id",
 *     description="アイテムID",
 *     type="number",
 *   ),
 *   @OA\Property(
 *     property="count",
 *     description="所持数",
 *     type="number",
 *   ),
 *   @OA\Property(
 *     property="property_ids",
 *     description="アイテムプロパティID配列",
 *     type="array",
 *     @OA\Items(
 *       description="アイテムプロパティID",
 *       type="number",
 *     ),
 *   ),
 *   @OA\Property(
 *     property="created_at",
 *     description="登録日時",
 *     type="string",
 *   ),
 *   @OA\Property(
 *     property="updated_at",
 *     description="更新日時",
 *     type="string",
 *   ),
 *   required={
 *     "id",
 *     "user_id",
 *     "item_id",
 *     "count",
 *     "property_ids",
 *     "created_at",
 *     "updated_at",
 *   },
 * )
 */
class UserItemController extends Controller
{
    /**
     * @OA\Get(
     *   path="/admin/users/{id}/items",
     *   summary="ユーザーアイテム一覧",
     *   description="ユーザーのアイテム一覧を取得する。",
     *   tags={
     *     "Admin",
     *   },
     *   security={
     *     {"SessionId":{}}
     *   },
     *   @OA\Parameter(
     *     in="path",
     *     name="id",
     *     description="ユーザーID",
     *     required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="成功",
     *     @OA\JsonContent(
     *       type="object",
     *       allOf={
     *         @OA\Schema(ref="#components/schemas/Pagination"),
     *         @OA\Schema(
     *           type="object",
     *           @OA\Property(
     *             property="data",
     *             description="データ配列",
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserItem")
     *           ),
     *         ),
     *       }
     *     ),
     *   ),
     * )
     */
    public function index($id)
    {
        return UserItem::where('user_id', $id)->orderBy('item_id', 'asc')->paginate(20);
    }
}