<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Coupon
 *
 * @author      Chema <chema@garridodiaz.com>
 * @package     Core
 * @copyright   (c) 2009-2013 Open Classifieds Team
 * @license     GPL v3
 */

class Model_Coupon extends ORM {

    /**
     * @var  string  Table name
     */
    protected $_table_name = 'coupons';

    /**
     * @var  string  PrimaryKey field name
     */
    protected $_primary_key = 'id_coupon';


    public function form_setup($form)
    {
       
    }

    public function exclude_fields()
    {
        return array('created');
    }

    /**
     * decreases de number available of coupon and deletes de cookie ;)
     * @param  model_coupon $coupon 
     * @return void         
     */
    public static function sale($coupon = NULL)
    {
        if ($coupon!=NULL)
        {
            if ($coupon->loaded())
            {
                $coupon->number_coupons--;
                $coupon->save();
                Session::instance()->set('coupon','');
            }
        }
    }


}