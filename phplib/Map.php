<?php

class Map {

	public static $map = array(
	    '/' => 'main.xml',
	    //news
	    'news' => 'news/list.xml',
	    'news/new' => 'news/new.xml',
	    'news/%d' => 'news/show.xml',
	    'news/%d/edit' => 'news/edit.xml',
	    // releases
	    'releases' => 'releases/list.xml',
	    'releases/new' => 'releases/new.xml',
	    'releases/%d' => 'releases/show.xml',
	    'releases/%d/edit' => 'releases/edit.xml',
	    // video
	    'video' => 'video/list.xml',
	    'video/new' => 'video/new.xml',
	    'video/%d' => 'video/show.xml',
	    'video/%d/edit' => 'video/edit.xml',
	    // mixes
	    'mix' => 'mix/list.xml',
	    'mix/new' => 'mix/new.xml',
	    'mix/%d' => 'mix/show.xml',
	    'mix/%d/edit' => 'mix/edit.xml',
	    // blog
	    'blog' => 'blog/list.xml',
	    'blog/new' => 'blog/new.xml',
	    'blog/%d' => 'blog/show.xml',
	    'blog/%d/edit' => 'blog/edit.xml',
	    'blog/%d/new' => 'blog/new.xml',
	    // other
	    'register' => 'register/index.xml',
	    'emailconfirm/%d/%s' => 'misc/email_confirm.xml',
	    404 => 'errors/p404.xml',
	    502 => 'errors/p502.xml',
	    // users
	    'user/%s' => 'users/user.xml',
	    'user/%s/edit' => 'users/edit.xml',
	);
	public static $sinonim = array(
	    'user/%d' => 'user/%s',
	    'user/%d/edit' => 'user/%s/edit',
	    'profile/%d' => 'user/%s',
	    'profile/%d/edit' => 'user/%s/edit',
	    'blog/%s' => 'blog/%d',
	    'blog/%s/new' => 'blog/%d/new',
	);

}
