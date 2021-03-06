<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Panel_Support extends Auth_Controller {

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Support'))->set_url(Route::url('oc-panel',array('controller'  => 'support'))));

    }

    public function action_index()
    {
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Tickets')));
        $this->template->title   = __('Support');

        $user = Auth::instance()->get_user();

        $tickets = new Model_Ticket();

        if ($user->id_role!=Model_Role::ROLE_ADMIN)
            $tickets->where('id_user','=',$user->id_user);


        $tickets = $tickets->where('id_ticket_parent', 'IS', NULL)
                        ->order_by('created','desc')
                        ->find_all();

        $this->template->bind('content', $content);
        $this->template->content = View::factory('oc-panel/pages/support/index',array('tickets'=>$tickets));
    }


    //creates new parent ticket
    public function action_new()
    {
        $errors = NULL;

        $user = Auth::instance()->get_user();

        //create new ticket
        if($_POST)
        {
            //if post save
            $id_order = core::post('order');

            //check if that order still have support...no cheating!! :D
            $order = new Model_Order();

            $order->where('id_order','=',$id_order)
                ->where('id_user','=',$user->id_user)
                ->where('support_date','>',DB::expr('NOW()'))
                ->where('status', '=', Model_Order::STATUS_PAID)
                ->limit(1)->find();
            

            $validation = Validation::factory($this->request->post())

                ->rule('title', 'not_empty')
                ->rule('title', 'min_length', array(':value', 2))
                ->rule('title', 'max_length', array(':value', 145))

                ->rule('description', 'not_empty')
                ->rule('description', 'min_length', array(':value', 50))
    
                ->rule('order', 'not_empty')
                ->rule('order', 'numeric');

            if ($validation->check() AND $order->loaded())
            {
                $ticket = new Model_Ticket();
                $ticket->id_user  = $user->id_user;
                $ticket->id_order = $id_order;
                $ticket->title    = core::post('title');
                $ticket->description    = core::post('description');

                $ticket->save();

                //send email to notify_url
                if(core::config('email.new_sale_notify'))
                {
                    Email::send(core::config('email.notify_email'), '', 'New Ticket: '.$ticket->title, 
                        Route::url('oc-panel',array('controller'=>'support','action'=>'ticket','id'=>$ticket->id_ticket)).'\n\n'.$ticket->description, 
                        core::config('email.notify_email'), '');
                }
                
                Alert::set(Alert::SUCCESS, __('Ticket created.'));
                $this->request->redirect(Route::url('oc-panel',array('controller'=>'support','action'=>'index')));
            }
            else
            {
                $errors = $validation->errors('ad');
            }
        }
        
        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('New Ticket')));
        $this->template->title   = __('New Ticket');
       

        //get orders with support
        $orders = new Model_Order();

        $orders = $orders->where('id_user','=',$user->id_user)
                        ->where('support_date','>',DB::expr('NOW()'))
                        ->where('status', '=', Model_Order::STATUS_PAID)
                        ->find_all();


        if ($orders->count() == 0)
        {
            Alert::set(Alert::ERROR, __('You do not have any purchase with support active.'));
            $this->request->redirect(Route::url('oc-panel',array('controller'=>'support','action'=>'index')));
        }

        $this->template->bind('content', $content);
        $this->template->content = View::factory('oc-panel/pages/support/new',array('orders'=>$orders));
        $content->errors = $errors;
    }


    //if post create a reply ticket
    public function action_ticket()
    {
        //after creating the reply we redirect to the ticket view
        $errors = NULL;

        $user = Auth::instance()->get_user();


        $ticket_id = $this->request->param('id',0);

        //getting the parent ticket
        $ticket = new Model_Ticket();

        if ($user->id_role!=Model_Role::ROLE_ADMIN)
            $ticket->where('id_user','=',$user->id_user);

        $ticket->where('id_ticket','=',$ticket_id)
            ->where('id_ticket_parent', 'IS', NULL)
            ->limit(1)
            ->find();
        if (!$ticket->loaded())
        {
            Alert::set(Alert::ERROR, __('Not your ticket.'));
            $this->request->redirect(Route::url('oc-panel',array('controller'=>'support','action'=>'index')));
        }

        //create new reply
        if($_POST)
        {
            $validation = Validation::factory($this->request->post())
                ->rule('description', 'not_empty')
                ->rule('description', 'min_length', array(':value', 5));

            if ($validation->check())
            {

                $ticketr = new Model_Ticket();
                $ticketr->id_user           = $user->id_user;
                $ticketr->id_order          = $ticket->id_order;
                $ticketr->id_ticket_parent  = $ticket->id_ticket;
                $ticketr->description       = core::post('description');

                $ticketr->save();

                //admin
                if ($user->id_role==Model_Role::ROLE_ADMIN)
                {
                    $ticket->id_user_support = $user->id_user;
                    $ticket->read_date = Date::unix2mysql();
                    $ticket->status = Model_Ticket::STATUS_HOLD;
                    $ticket->save();

                    //send email to creator of the ticket
                    $ticket->user->email('new.reply',array('[TITLE]'=>$ticket->title,
                                                    '[DESCRIPTION]'=>$ticketr->description,
                                                    '[URL.QL]'=>$ticket->user->ql('oc-panel',array('controller'=>'support','action'=>'ticket','id'=>$ticket->id_ticket),TRUE))
                                            );

                }
                //send email to notify_url
                elseif(core::config('email.new_sale_notify'))
                {
                    Email::content(core::config('email.notify_email'), NULL, NULL,NULL, 'new.reply', array('[TITLE]'=>$ticket->title,
                                                    '[DESCRIPTION]'=>$ticketr->description,
                                                    '[URL.QL]'=>$user->ql('oc-panel',array('controller'=>'support','action'=>'ticket','id'=>$ticket->id_ticket),TRUE)));
                   
                }
                
                Alert::set(Alert::SUCCESS, __('Reply created.'));
            }
            else
            {
                $errors = $validation->errors('ad');
            }
        }

        //getting all the ticket replies
        $replies = new Model_Ticket();
        $replies = $replies->where('id_ticket_parent','=',$ticket->id_ticket)
                    ->order_by('created','asc')
                    ->find_all();

       
        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Ticket')));
        $this->template->title   = $ticket->title.' - '.__('Ticket');

        $this->template->bind('content', $content);
        $this->template->content = View::factory('oc-panel/pages/support/ticket',array('replies'=>$replies,'ticket'=>$ticket));
        $content->errors = $errors;

    }



    //ticket conversation display
    public function action_close()
    {
        $user = Auth::instance()->get_user();

        $ticket_id = $this->request->param('id',0);

        //getting the parent ticket
        $ticket = new Model_Ticket();

        //admin can
        if ($user->id_role!=Model_Role::ROLE_ADMIN)
            $ticket->where('id_user','=',$user->id_user);

        $ticket->where('id_ticket','=',$ticket_id)
            ->where('id_ticket_parent', 'IS', NULL)
            ->limit(1)
            ->find();
        if (!$ticket->loaded())
        {
            Alert::set(Alert::ERROR, __('Not your ticket.'));
            $this->request->redirect(Route::url('oc-panel',array('controller'=>'support','action'=>'index')));
        }
        else
        {
            //close ticket
            $ticket->status = Model_Ticket::STATUS_CLOSED;
            $ticket->save();

            Alert::set(Alert::SUCCESS, __('Ticket closed.'));
            $this->request->redirect(Route::url('oc-panel',array('controller'=>'support','action'=>'index')));
        }
        
    }

            



}
