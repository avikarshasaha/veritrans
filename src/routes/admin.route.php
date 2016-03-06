<?php

$app->group('/admin', function () use ($app, $settings, $isLogged, $authenticate) {
    $app->get('/login/', $isLogged($app, $settings), function() use ($app) {
        $flash = $app->view()->getData('flash');
        $error = isset($flash['error']) ? $flash['error'] : '';

        $app->render('a_login.html', array('error' => $error));
    });

    $app->post('/login', function() use ($app, $settings) {
        $username = $app->request->post('form-username');
        $password = hash('sha512', $app->request->post('form-password'));
        $user = Users::whereRaw('username = ? AND password = ?', array($username, $password))->get();

        if ($user->count() != 0) {
            $_SESSION['user'] = $username;
            $app->redirect($settings->base_url . '/admin');
        } else {
            $app->flash('error', 1);
            $app->redirect($settings->base_url . '/admin/login');
        }
    });

    $app->get('/logout/', $authenticate($app, $settings), function() use ($app, $settings) {
        unset($_SESSION['user']);
        $app->view()->setData('user', null);
        $app->redirect($settings->base_url.'/admin');
    });

    $app->get('/', $authenticate($app, $settings), function() use ($app) {
        $app->render('a_home.html');
    });

    $app->get('/orders', $authenticate($app, $settings), function() use ($app) {
        $orders = Orders::orderBy('created_time', 'desc')->get();
        $app->render('a_orders.html', array('orders' => $orders));
    });

    $app->get('/order/:id', $authenticate($app, $settings), function($id) use ($app) {
        $order = Orders::where('id', '=', $id)->first();
        $jobs = Jobs::where('order_id', '=', $id)->get();
        $data = array();

        if($order){
            foreach ($jobs as $job) {
              $data[] = unserialize($job['dump']);
            }
            // echo "<pre>";
            // print_r($data);
            // echo "</pre>";
            $app->render('a_order.html', array('order' => $order, 'jobs' => $data));
        }
    })->conditions(array('id' => '\d+'));

    $app->get('/orderdumps', $authenticate($app, $settings), function() use ($app) {
        $order_dumps = OrderDump::groupBy('session_id')->get();
        $data = array();
        $app->render('a_order_dumps.html', array('order_dumps' => $order_dumps));
    });

    $app->get('/orderdump/:id', $authenticate($app, $settings), function($id) use ($app) {
        $order_dumps = OrderDump::where('session_id', '=', $id)->get();
        $data = array();

        if($order_dumps){
            foreach ($order_dumps as $order_dump) {
              $data[] = unserialize($order_dump['order_data']);
            }
             // echo "<pre>";
            // print_r($data);
            // echo "</pre>";
            $app->render('a_order_dump.html', array('order_dumps' => $data));
        }
    });

    $app->get('/contact_requests', $authenticate($app, $settings), function() use ($app) {
        $contact_requests = ContactUs::orderBy('created_at', 'desc')->get();
        $app->render('a_contact_requests.html', array('contact_requests' => $contact_requests));
    });

    $app->get('/newsletter', $authenticate($app, $settings), function() use ($app) {
        $newsletters = Newsletter::orderBy('id', 'asc')->get();
        $app->render('a_newsletters.html', array('newsletters' => $newsletters));
    });

    $app->get('/purchasecodes', function() use ($app, $settings) {
        $purchasecodes = PurchaseCodes::orderBy('id', 'asc')->get();
        $app->render('a_purchasecodes.html', array('purchasecodes' => $purchasecodes));
    });

    $app->post('/set/purchasecode', function() use ($app, $settings) {
        $post = $app->request->post();
        PurchaseCodes::insert(array('purchase_code' => md5(uniqid()), 'template' => $post['template']));
        $app->redirect($settings->base_url."/admin/purchasecodes"); 
    });

    $app->get('/scandir', function() use ($app, $settings) {
        print_r(array_diff(scandir('email-templates'), array('..', '.')));
    });
    
});
