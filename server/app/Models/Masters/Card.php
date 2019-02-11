<?php

namespace App\Models\Masters;

/**
 * カードのマスタを表すモデル。
 *
 * カードはユーザーが所持するもので、ユーザーはカードでデッキを組んで敵と戦う。
 * （味方キャラクターや召喚獣などのイメージ。）
 *
 * カードは以下のようなステータスを持つ。
 * ・最大HP
 * ・攻撃力
 * ・防御力
 * ・素早さ
 */
class Card extends MasterModel
{
    /**
     * ネイティブなタイプへキャストする属性。
     * @var array
     */
    protected $casts = [
        'rarity' => 'integer',
        'max_hp' => 'integer',
        'attack' => 'integer',
        'defense' => 'integer',
        'agility' => 'integer',
    ];
}
