<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Models\Masters\MasterModel;

/**
 * マスターコントローラ。
 *
 * @OA\Tag(
 *   name="Masters",
 *   description="マスタAPI",
 * )
 */
class MasterController extends Controller
{
    /**
     * @OA\Get(
     *   path="/masters",
     *   summary="マスタ一覧",
     *   description="マスタ一覧を取得する。",
     *   tags={
     *     "Masters",
     *   },
     *   @OA\Response(
     *     response=200,
     *     description="マスタ一覧",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(type="string"),
     *     ),
     *   ),
     * )
     */
    public function index()
    {
        return MasterModel::getMasterModels()->map(function ($classname) {
            return (new \ReflectionClass($classname))->getShortName();
        });
    }

    /**
     * @OA\Get(
     *   path="/masters/{name}",
     *   summary="マスタ取得",
     *   description="公開中のマスタのレコードを取得する",
     *   tags={
     *     "Masters",
     *   },
     *   @OA\Parameter(
     *     in="path",
     *     name="name",
     *     description="マスタ名",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="マスタ配列",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(
     *         description="マスタ",
     *         type="object",
     *       ),
     *     ),
     *   ),
     * )
     */
    public function findMaster(string $name)
    {
        // ユーザーに先の予定などが見えてしまわないよう、公開期間などがあるマスタは有効なものだけ返す
        // （モデル側にisActive関数という形で実装する）
        $classname = MasterModel::getMasterModel($name);
        if (!$classname) {
            throw new BadRequestException("name={$name} is not found");
        }
        $all = $classname::all();
        return method_exists($classname, 'isActive') ? $all->active() : $all;
    }
}
