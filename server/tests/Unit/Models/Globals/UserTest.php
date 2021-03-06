<?php

namespace Tests\Unit\Models\Globals;

use Tests\TestCase;
use App\Exceptions\EmptyResourceException;
use App\Models\Globals\User;
use App\Models\Virtual\ObjectInfo;

class UserTest extends TestCase
{
    /**
     * 課金コイン更新のテスト。
     */
    public function testSpecialCoins() : void
    {
        $user = new User();

        // 通常は普通のプロパティとして機能
        $this->assertSame(0, $user->special_coins);
        $this->assertSame(0, $user->paid_special_coins);

        $user->special_coins = 1000;
        $user->paid_special_coins = 100;
        $this->assertSame(1000, $user->special_coins);
        $this->assertSame(100, $user->paid_special_coins);

        $user->special_coins = 200;
        $this->assertSame(200, $user->special_coins);
        $this->assertSame(100, $user->paid_special_coins);

        $user->paid_special_coins = 40;
        $this->assertSame(200, $user->special_coins);
        $this->assertSame(40, $user->paid_special_coins);

        // 値を減らす場合、無償分で足りなければ、自動的に有償分から減らす
        $user->special_coins = -30;
        $this->assertSame(0, $user->special_coins);
        $this->assertSame(10, $user->paid_special_coins);

        $user->special_coins = -10;
        $this->assertSame(0, $user->special_coins);
        $this->assertSame(0, $user->paid_special_coins);
    }

    /**
     * 課金コイン更新のテスト（マイナス値）。
     */
    public function testSpecialCoinsAtMinusValue() : void
    {
        // 値がマイナスになる場合は例外
        $this->expectException(EmptyResourceException::class);
        $user = new User();
        $user->special_coins = -1;
    }

    /**
     * 課金コイン更新のテスト（マイナス値）。
     */
    public function testSpecialCoinsAtMinusValueWithPaid() : void
    {
        // 有償があっても、マイナスになる場合はNG
        $this->expectException(EmptyResourceException::class);
        $user = new User();
        $user->special_coins = 200;
        $user->paid_special_coins = 40;
        $user->special_coins = -41;
    }

    /**
     * 有償課金コイン更新のテスト（マイナス値）。
     */
    public function testPaidSpecialCoinsAtMinusValue() : void
    {
        // 値がマイナスになる場合は例外
        $this->expectException(EmptyResourceException::class);
        $user = new User();
        $user->paid_special_coins = -1;
    }

    /**
     * レベル更新のテスト。
     */
    public function testLevel() : void
    {
        // レベル上昇時にスタミナが回復すること
        $user = new User();
        $this->assertSame(1, $user->level);
        $this->assertSame(20, $user->stamina);

        $user->level = 2;
        $this->assertSame(2, $user->level);
        $this->assertSame(45, $user->stamina);

        $user->level = 5;
        $this->assertSame(5, $user->level);
        $this->assertSame(150, $user->stamina);

        // 万が一レベルが下がっても減ることは無い
        $user->level = 2;
        $this->assertSame(2, $user->level);
        $this->assertSame(150, $user->stamina);
    }

    /**
     * 経験値更新のテスト。
     */
    public function testExp() : void
    {
        // 経験値獲得時にレベルが上昇すること
        $user = new User();

        $this->assertSame(1, $user->level);

        $user->exp = 100;
        $this->assertSame(2, $user->level);

        $user->exp = 1000;
        $this->assertSame(6, $user->level);

        // 一度上がったレベルは自動では下がらない
        $user->exp = 100;
        $this->assertSame(6, $user->level);
    }

    /**
     * スタミナ回復のテスト。
     */
    public function testStamina() : void
    {
        $user = new User();

        // 初期状態（スタミナ0、更新時間なし）では、LVのMAX値が返る
        $this->assertSame(20, $user->stamina);

        // 値が設定されると、その時点の値が返るようになる
        $user->stamina = 5;
        $this->assertSame(5, $user->stamina);

        // 時間が経過すると、経過時間に応じて回復
        // （回復量はマスタに応じて。テスト用マスタでは4分に1回復。端数切捨て）
        $this->setTestNow('+4 minutes');
        $this->assertSame(6, $user->stamina);
        $this->setTestNow('+3 minutes 59 seconds');
        $this->assertSame(6, $user->stamina);
        $this->setTestNow('+1 second');
        $this->assertSame(7, $user->stamina);

        // LVの最大値は越えない
        $this->setTestNow('+1 day');
        $this->assertSame(20, $user->stamina);

        // 意図的に最大値越えの値を入れた場合、その値は維持される
        $user->stamina = 100;
        $this->assertSame(100, $user->stamina);
    }

    /**
     * スタミナ更新のテスト（マイナス値）。
     */
    public function testStaminaAtMinusValue() : void
    {
        // 値がマイナスになる場合は例外
        $this->expectException(EmptyResourceException::class);
        $user = new User();
        $user->stamina = -1;
    }

    /**
     * ゲームコインを受け取るのテスト。
     */
    public function testReceiveGameCoinTo() : void
    {
        $user = factory(User::class)->create();

        $received = User::receiveGameCoinTo($user->id, new ObjectInfo(['count' => 1000]));

        $this->assertNull($received->id);
        $this->assertSame(1000, $received->count);
        $this->assertSame($user->game_coins + 1000, $received->total);
        $this->assertFalse($received->is_new);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'game_coins' => $user->game_coins + 1000,
        ]);
    }

    /**
     * スペシャルコインを受け取るのテスト。
     */
    public function testReceiveSpecialCoinTo() : void
    {
        $user = factory(User::class)->create();

        $received = User::receiveSpecialCoinTo($user->id, new ObjectInfo(['count' => 1000]));

        $this->assertNull($received->id);
        $this->assertSame(1000, $received->count);
        $this->assertSame($user->special_coins + $user->paid_special_coins + 1000, $received->total);
        $this->assertFalse($received->is_new);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'special_coins' => $user->special_coins + 1000,
            'paid_special_coins' => $user->paid_special_coins,
        ]);
    }

    /**
     * ユーザー経験値を受け取るのテスト。
     */
    public function testReceiveExpTo() : void
    {
        $user = factory(User::class)->create();

        $received = User::receiveExpTo($user->id, new ObjectInfo(['count' => 1000]));

        $this->assertNull($received->id);
        $this->assertSame(1000, $received->count);
        $this->assertSame($user->exp + 1000, $received->total);
        $this->assertFalse($received->is_new);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'exp' => $user->exp + 1000,
        ]);
    }
}
