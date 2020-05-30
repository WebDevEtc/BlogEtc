<?php

namespace WebDevEtc\BlogEtc\Gates;

class GateTypes
{
    // Gate to allow admins to manage the blog posts and see unpublished posts:
    public const MANAGE_BLOG_ADMIN = 'blogetc-manage-admin';

    // Default ones which always return true, but you can override:
    public const ADD_COMMENT = 'blogetc-add-comments';
}
