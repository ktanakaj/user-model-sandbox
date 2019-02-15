<?php

namespace App\Models;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * JSON API向けに拡張したLengthAwarePaginator。
 */
class JsonLengthAwarePaginator extends LengthAwarePaginator
{
    /**
     * インスタンスを連想配列に変換する。
     */
    public function toArray() : array
    {
        // JSON APIでは不要なプロパティをカット&文字列が入りやすい項目をキャスト
        return [
            'per_page' => intval($this->perPage()),
            'total' => $this->total(),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
            'data' => $this->items->toArray(),
        ];
    }
}
