<?php defined('SYSPATH') or die('No direct script access.');?>

	
		 <?=Form::errors()?>
		<div class="page-header">
			<h1><?=__('Payments Configuration')?></h1>
            <p class=""><?=__('List of payment configuration values. Replace input fields with new desired values.')?></p>

		</div>


		<div class="well">
		<?= FORM::open(Route::url('oc-panel',array('controller'=>'settings', 'action'=>'payment')), array('class'=>'form-horizontal', 'enctype'=>'multipart/form-data'))?>
			<fieldset>
				<?foreach ($config as $c):?>
					<?$forms[$c->config_key] = array('key'=>$c->config_key, 'value'=>$c->config_value)?>
				<?endforeach?>

                <div class="control-group">
                    <?= FORM::label($forms['paypal_account']['key'], __('Paypal account'), array('class'=>'control-label', 'for'=>$forms['paypal_account']['key']))?>
                    <div class="controls">
                        <?= FORM::input($forms['paypal_account']['key'], $forms['paypal_account']['value'], array(
                        'placeholder' => "some@email.com", 
                        'class' => 'tips', 
                        'id' => $forms['paypal_account']['key'],
                        'data-content'=> __("Paypal mail address"),
                        'data-trigger'=>"hover",
                        'data-placement'=>"right",
                        'data-toggle'=>"popover",
                        'data-original-title'=>__("The paypal email address where the payments will be sent"), 
                        ))?> 
                        </div>
                </div>

				<div class="control-group">
					<?= FORM::label($forms['sandbox']['key'], __('Sandbox'), array('class'=>'control-label', 'for'=>$forms['sandbox']['key']))?>
					<div class="controls">
						<?= FORM::select($forms['sandbox']['key'], array(FALSE=>"FALSE",TRUE=>"TRUE"),$forms['sandbox']['value'], array(
						'placeholder' => "TRUE or FALSE", 
						'class' => 'tipsti', 
						'id' => $forms['sandbox']['key'],
						'data-content'=> '',
						'data-trigger'=>"hover",
						'data-placement'=>"right",
						'data-toggle'=>"popover",
						'data-original-title'=>'', 
						))?> 
					</div>
				</div>
                 <div class="control-group">
                <?= FORM::label($forms['thanks_page']['key'], __('Paypal thanks page'), array('class'=>'control-label', 'for'=>$forms['thanks_page']['key']))?>
                <div class="controls">
                    <?= FORM::select($forms['thanks_page']['key'], $pages, $forms['thanks_page']['value'], array( 
                    'class' => 'tips', 
                    'id' => $forms['thanks_page']['key'], 
                    'data-content'=> __("Select which page you want to redirect the user after a success paypal payment, be sure to mention to check their paypal account for an email."),
                    'data-trigger'=>"hover",
                    'data-placement'=>"right",
                    'data-toggle'=>"popover",
                    'data-original-title'=>__("Thanks for the Paypal payment page"),
                    ))?> 
                </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                    <label>
                        <p>To get paid via Credit card you need a Paymill account. It's free to register. They charge 2'95% of any sale.</p>
                        <a class="btn btn-success" target="_blank" href="https://app.paymill.com/en-en/auth/register?referrer=openclassifieds">
                            <i class="icon-pencil icon-white"></i> Register for free at Paymill</a>
                    </label>
                    </div>
                </div>
                <div class="control-group">
                    
                    <?= FORM::label($forms['paymill_private']['key'], __('Paymill private key'), array('class'=>'control-label', 'for'=>$forms['paymill_private']['key']))?>
                    <div class="controls">
                        <?= FORM::input($forms['paymill_private']['key'], $forms['paymill_private']['value'], array(
                        'placeholder' => "", 
                        'class' => 'tips', 
                        'id' => $forms['paymill_private']['key'],
                        'data-content'=> __("Paymill private key"),
                        'data-trigger'=>"hover",
                        'data-placement'=>"right",
                        'data-toggle'=>"popover",
                        'data-original-title'=>'', 
                        ))?> 
                        </div>
                </div>

                <div class="control-group">
                    <?= FORM::label($forms['paymill_public']['key'], __('Paymill public key'), array('class'=>'control-label', 'for'=>$forms['paymill_public']['key']))?>
                    <div class="controls">
                        <?= FORM::input($forms['paymill_public']['key'], $forms['paymill_public']['value'], array(
                        'placeholder' => "", 
                        'class' => 'tips', 
                        'id' => $forms['paymill_public']['key'],
                        'data-content'=> __("Paymill public key"),
                        'data-trigger'=>"hover",
                        'data-placement'=>"right",
                        'data-toggle'=>"popover",
                        'data-original-title'=>'', 
                        ))?> 
                        </div>
                </div>

               
				<div class="form-actions">
					<?= FORM::button('submit', 'Update', array('type'=>'submit', 'class'=>'btn-small btn-primary', 'action'=>Route::url('oc-panel',array('controller'=>'settings', 'action'=>'payment'))))?>
				</div>
			</fieldset>	
	</div><!--end span10-->
