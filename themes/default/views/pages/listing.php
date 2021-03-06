<?php defined('SYSPATH') or die('No direct script access.');?>
     
<?if ($category!==NULL):?>
<?if (strlen($category->description>0)):?>
<div class="well advise clearfix" id="advise">
    <p><?=Text::bb2html($category->description,TRUE)?></p> 
</div><!--end of advise-->
<?endif?>
<?endif?>

    <?if(count($products)):?>
        <div class="row-fluid">
        <ul class="thumbnails">
        <?$i=0;
        foreach($products as $product ):?>
            <?if ($i%3==0):?></ul></div><div class="row-fluid"><ul class="thumbnails"><?endif?>
            <li class="span4">
                <div class="thumbnail">

                    <?if($product->get_first_image() !== NULL):?>
                        <a title="<?= $product->title;?>" href="<?=Route::url('product', array('seotitle'=>$product->seotitle,'category'=>$product->category->seoname))?>">
                            <img width="300px" height="200px" src="<?=URL::base('http')?><?=$product->get_first_image()?>" class="" >
                        </a>
                    <?endif?>

                    <div class="caption">
                        <h5><a href="<?=Route::url('product', array('seotitle'=>$product->seotitle,'category'=>$product->category->seoname))?>"><?=substr($product->title, 0, 30)?></a></h5>
                        <p><?=substr(Text::removebbcode($product->description), 0, 30)?></p>
                        <a class="btn btn-success" href="<?=Route::url('product', array('seotitle'=>$product->seotitle,'category'=>$product->category->seoname))?>">
                        <?if ($product->final_price()>0):?>
                            <?=__('Buy Now')?> <?=$product->final_price().' '.$product->currency?>
                        <?elseif(!empty($product->file_name)):?>
                            <?=__('Free Download')?>
                        <?else:?>
                            <?=__('Get it for Free')?>
                        <?endif?>
                        </a>
                    </div>
                </div>
            </li>
        <?$i++?>
    <?endforeach?>
    </ul>
</div><!--/row-->

<?=$pagination?>
<?elseif (count($products) == 0):?>
<!-- Case when we dont have products for specific category / location -->
<div class="page-header">
    <h3><?=__('We do not have any product in this category')?></h3>
</div>

<?endif?>
