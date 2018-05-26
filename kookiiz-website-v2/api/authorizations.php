<?php
    /*******************************************************
    Title: Authorizations
    Authors: Kookiiz Team
    Purpose: Define authorizations for API actions
    ********************************************************/

    //Authorization levels
    define('API_AUTH_PUBLIC', 0);       //anyone
    define('API_AUTH_MEMBER', 1);       //registered members
    define('API_AUTH_ADMIN', 2);        //Kookiiz administrator
    define('API_AUTH_ADMINSUP', 3);     //Kookiiz super administrator

    //Authorizations by API module and action
    $API_AUTHORIZATIONS = array(
        'articles'      => array(
            'delete'        => API_AUTH_ADMIN,
            'edit'          => API_AUTH_ADMIN,
            'history'       => API_AUTH_PUBLIC,
            'load'          => API_AUTH_PUBLIC,
            'save'          => API_AUTH_ADMIN,
            'search'        => API_AUTH_PUBLIC
        ),
        'chefs'         => array(
            'load'          => API_AUTH_PUBLIC
        ),
        'comments'      => array(
            'delete'        => API_AUTH_MEMBER,
            'edit'          => API_AUTH_MEMBER,
            'load'          => API_AUTH_PUBLIC,
            'rate'          => API_AUTH_MEMBER,
            'save'          => API_AUTH_MEMBER
        ),
        'email'         => array(
            'check'         => API_AUTH_PUBLIC
        ),
        'events'        => array(
            'load'          => API_AUTH_MEMBER
        ),
        'feedback'      => array(
            'delete'        => API_AUTH_ADMIN,
            'enable'        => API_AUTH_ADMIN,
            'load'          => API_AUTH_ADMIN,
            'question'      => API_AUTH_PUBLIC,
            'save'          => API_AUTH_PUBLIC,
            'stats'         => API_AUTH_ADMIN
        ),
        'friends'       => array(
            'add'           => API_AUTH_MEMBER,
            'block'         => API_AUTH_MEMBER,
            'deny'          => API_AUTH_MEMBER,
            'remove'        => API_AUTH_MEMBER,
            'share'         => API_AUTH_MEMBER,
            'unshare'       => API_AUTH_MEMBER
        ),
        'glossary'      => array(
            'add'           => API_AUTH_ADMIN,
            'delete'        => API_AUTH_ADMIN,
            'edit'          => API_AUTH_ADMIN,
            'search'        => API_AUTH_PUBLIC,
            'search_recipe' => API_AUTH_PUBLIC
        ),
        'ingredients'   => array(
            'load'          => API_AUTH_PUBLIC,
            'season_create' => API_AUTH_ADMIN
        ),
        'invitations'   => array(
            'create'        => API_AUTH_MEMBER,
            'delete'        => API_AUTH_MEMBER,
            'respond'       => API_AUTH_MEMBER,
            'save'          => API_AUTH_MEMBER
        ),
        'news'          => array(
            'load'          => API_AUTH_PUBLIC
        ),
        'partners'      => array(
            'add'           => API_AUTH_ADMIN,
            'delete'        => API_AUTH_ADMIN,
            'edit'          => API_AUTH_ADMIN,
            'list'          => API_AUTH_ADMIN,
            'load'          => API_AUTH_PUBLIC
        ),
        'pictures'      => array(
            'delete'        => API_AUTH_PUBLIC
        ),
        'quickmeals'    => array(
            'create'        => API_AUTH_MEMBER,
            'delete'        => API_AUTH_MEMBER
        ),
        'recipes'       => array(
            'check_title'   => API_AUTH_MEMBER,
            'delete'        => API_AUTH_ADMINSUP,
            'dismiss'       => API_AUTH_ADMIN,
            'edit'          => API_AUTH_MEMBER,
            'express'       => API_AUTH_PUBLIC,
            'list_dismiss'  => API_AUTH_ADMINSUP,
            'load'          => API_AUTH_PUBLIC,
            'rate'          => API_AUTH_MEMBER,
            'report'        => API_AUTH_MEMBER,
            'save'          => API_AUTH_MEMBER,
            'save_pic'      => API_AUTH_MEMBER,
            'search'        => API_AUTH_PUBLIC,
            'translate'     => API_AUTH_MEMBER,
            'validate'      => API_AUTH_ADMINSUP
        ),
        'session'       => array(
            'lang_change'   => API_AUTH_PUBLIC,
            'load'          => API_AUTH_PUBLIC,
            'login'         => API_AUTH_PUBLIC,
			'logout'		=> API_AUTH_PUBLIC,
            'pass_reset'    => API_AUTH_PUBLIC,
            'save'          => API_AUTH_MEMBER,
            'update'        => API_AUTH_PUBLIC
        ),
        'shopping'      => array(
            'market_create' => API_AUTH_MEMBER,
            'market_delete' => API_AUTH_MEMBER,
            'market_select' => API_AUTH_MEMBER,
            'market_save'   => API_AUTH_MEMBER,
            'send'          => API_AUTH_MEMBER
        ),
        'users'         => array(
            'admin_elect'   => API_AUTH_ADMINSUP,
            'delete'        => API_AUTH_MEMBER,
            'network_info'  => API_AUTH_PUBLIC,
            'pic_save'      => API_AUTH_MEMBER,
            'preview'       => API_AUTH_PUBLIC,
            'search'        => API_AUTH_MEMBER
        )
    );
?>