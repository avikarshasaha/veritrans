<?php
$app->get('/post/:id', function($id) use ($app) {
    if ($post = Posts::find($id)) {
        $flash = $app->view()->getData('flash');
        $error = isset($flash['error']) ? $flash['error'] : '';

        $post->author = Users::get_author($post->user_id);
        $post->date = date('d-m-Y H:i', $post->creation);
        $post->text = $app->markdown->parse($post->text);

        $lines = Lines::where('post_id', '=', $id)->get();
        $notes = Notes::where('post_id', '=', $id)->get();

        $redirect = $app->request->getUrl() . $app->request->getPath();

        $app->render('post.html', array('post' => $post, 'error' => $error, 'lines' => $lines, 'notes' =>$notes, 'redirect' => $redirect));
    }
    else {
        $app->render('404_post.html');
    }
})->conditions(array('page' => '\d+'));
