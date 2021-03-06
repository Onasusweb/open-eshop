<?php defined('SYSPATH') or die('No direct script access.');?>

<form class="well form-inline"  method="post" action="<?=URL::current()?>">         
    <?if (Controller::$coupon!==NULL):?>
        <?=Form::hidden('coupon_delete',Controller::$coupon->name)?>
        <button type="submit" class="btn btn-warning"><?=__('Delete')?> <?=Controller::$coupon->name?></button>
        <p>
            <?=__('Discount off')?> <?=(Controller::$coupon->discount_amount==0)?round(Controller::$coupon->discount_percentage,0).'%':round(Controller::$coupon->discount_amount,0)?> <br>
            <?=Controller::$coupon->number_coupons?> <?=__('coupons left')?>, <?=__('valid until')?> <?=Controller::$coupon->valid_date?>.
        </p>
    <?else:?>
        <input class="input-medium" type="text" name="coupon" value="<?=Core::get('coupon')?><?=Core::get('coupon')?>" placeholder="<?=__('Coupon Name')?>">          
        <button type="submit" class="btn btn-primary"><?=__('Add')?></button>
    <?endif?>      	
</form>